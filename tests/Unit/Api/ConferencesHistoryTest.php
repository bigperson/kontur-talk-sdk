<?php

namespace Kontur\Talk\Tests\Unit\Api;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kontur\Talk\Api\ConferencesHistory;
use Kontur\Talk\Exception\TalkNotFoundException;

class ConferencesHistoryTest extends ApiTestCase
{
    public function testListWithNoFiltersSendsNoQuery(): void
    {
        $response = ['conferences' => []];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $conferencesHistory = new ConferencesHistory($client);

        $result = $conferencesHistory->list();

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/domain/conferencesHistory', $request->getUri()->getPath());
        $this->assertSame('', $request->getUri()->getQuery());
    }

    public function testListSendsRoomNameAsRepeatedQueryParam(): void
    {
        // Форма ответа — TalkConferenceInfos из OpenAPI-спеки
        $response = [
            'conferences' => [
                [
                    'key' => 'conf-1',
                    'roomName' => 'sales-room',
                    'startTime' => '2026-09-01T10:00:00Z',
                    'endTime' => '2026-09-01T10:45:00Z',
                    'title' => 'Демо для клиента',
                    'isPlannedMeeting' => true,
                ],
            ],
        ];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $conferencesHistory = new ConferencesHistory($client);

        $result = $conferencesHistory->list(
            '2026-09-01T00:00:00Z',
            '2026-09-30T00:00:00Z',
            ['sales-room', 'support-room'],
            0,
            50
        );

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame([
            'fromDate' => '2026-09-01T00:00:00Z',
            'toDate' => '2026-09-30T00:00:00Z',
            'roomName' => ['sales-room', 'support-room'],
            'skip' => '0',
            'take' => '50',
        ], $this->queryParams($request));

        // Явно проверяем нотацию на проводе: спецификация объявляет roomName без style/explode,
        // то есть по умолчанию OpenAPI 3.0 (style: form, explode: true) — голый повтор ключа
        // roomName=...&roomName=..., а не индексные скобки roomName[0]=... и не пустые скобки
        // roomName[]=...
        $this->assertSame(
            'fromDate=2026-09-01T00%3A00%3A00Z&toDate=2026-09-30T00%3A00%3A00Z'
                . '&roomName=sales-room&roomName=support-room&skip=0&take=50',
            $request->getUri()->getQuery()
        );
    }

    public function testGetCallsCorrectEndpoint(): void
    {
        $response = [
            'key' => 'conf-1',
            'roomName' => 'sales-room',
            'startTime' => '2026-09-01T10:00:00Z',
            'endTime' => '2026-09-01T10:45:00Z',
            'title' => 'Демо для клиента',
            'isPlannedMeeting' => true,
            'artifacts' => [
                'participants' => [],
                'invitedParticipants' => [],
                'content' => [],
                'title' => null,
            ],
        ];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $conferencesHistory = new ConferencesHistory($client);

        $result = $conferencesHistory->get('conf-1');

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/ConferencesHistory/conf-1', $request->getUri()->getPath());
    }

    public function testGetThrowsNotFoundOn404(): void
    {
        $this->expectException(TalkNotFoundException::class);

        $exception = new ClientException(
            'Not found',
            new Request('GET', 'ConferencesHistory/missing'),
            new Response(404)
        );

        $client = $this->mockClient([$exception]);
        $conferencesHistory = new ConferencesHistory($client);

        $conferencesHistory->get('missing');
    }

    public function testGetArtifactsCallsV2EndpointAndDecodesRecordings(): void
    {
        // Форма ответа — TalkEnrichedConference: artifacts.recordings[] = TalkConferenceRecording
        $response = [
            'key' => 'conf-1',
            'roomName' => 'sales-room',
            'startTime' => '2026-09-01T10:00:00Z',
            'endTime' => '2026-09-01T10:45:00Z',
            'title' => 'Демо для клиента',
            'isPlannedMeeting' => true,
            'artifacts' => [
                'title' => null,
                'participants' => [],
                'invitedParticipants' => [],
                'polls' => [],
                'notes' => [],
                'whiteboards' => [],
                'recordings' => [
                    [
                        'id' => 'rec-1',
                        'title' => 'Демо для клиента.mp4',
                        'createdDate' => '2026-09-01T10:45:00Z',
                        'duration' => 2700,
                        'status' => 'complete',
                        'allowAnonymousAccess' => false,
                    ],
                ],
            ],
        ];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $conferencesHistory = new ConferencesHistory($client);

        $result = $conferencesHistory->getArtifacts('conf-1');

        $this->assertEquals($response, $result);
        $this->assertSame('rec-1', $result['artifacts']['recordings'][0]['id']);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/ConferencesHistory/v2/conf-1', $request->getUri()->getPath());
    }
}
