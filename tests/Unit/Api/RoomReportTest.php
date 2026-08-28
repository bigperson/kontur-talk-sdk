<?php

namespace Kontur\Talk\Tests\Unit\Api;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kontur\Talk\Api\RoomReport;
use Kontur\Talk\Exception\TalkNotFoundException;

class RoomReportTest extends ApiTestCase
{
    public function testStatisticsReportCallsCorrectEndpointAndDecodesParticipants(): void
    {
        // Форма ответа — RoomStatisticsReport из OpenAPI-спеки
        $response = [
            'roomName' => 'sales-room',
            'from' => '2026-09-01T00:00:00Z',
            'to' => '2026-09-30T00:00:00Z',
            'participantCount' => 2,
            'participantTotalDurations' => [
                [
                    'participantName' => 'Анна Петрова',
                    'participant' => 'Участник',
                    'isGuest' => false,
                    'participantEmail' => 'anna@example.com',
                    'sessionHall' => null,
                    'duration' => '00:45:00',
                ],
            ],
            'roomParticipants' => [
                [
                    'participantName' => 'Анна Петрова',
                    'participantId' => 'user-1',
                    'isGuest' => false,
                    'participant' => 'Участник',
                    'participantEmail' => 'anna@example.com',
                    'sessionHall' => null,
                    'entryTime' => '2026-09-01T10:00:00Z',
                    'exitTime' => '2026-09-01T10:45:00Z',
                    'urlParams' => null,
                ],
                [
                    'participantName' => 'Клиент',
                    'participantId' => 'guest-1',
                    'isGuest' => true,
                    'participant' => 'Гость',
                    'participantEmail' => null,
                    'sessionHall' => null,
                    'entryTime' => '2026-09-01T10:02:00Z',
                    // Участник ещё в комнате — время выхода приходит пустым
                    'exitTime' => null,
                    'urlParams' => null,
                ],
            ],
            'streamViewers' => [],
            'metrics' => [
                'streamViewersCount' => 0,
                'streamViewersBuildSecondsDuration' => 0,
            ],
        ];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $roomReport = new RoomReport($client);

        $result = $roomReport->statisticsReport('sales-room', '2026-09-01T00:00:00Z', '2026-09-30T00:00:00Z');

        $this->assertEquals($response, $result);
        $this->assertSame('2026-09-01T10:00:00Z', $result['roomParticipants'][0]['entryTime']);
        $this->assertNull($result['roomParticipants'][1]['exitTime']);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/RoomReport/sales-room/statistics/report', $request->getUri()->getPath());
        $this->assertSame([
            'from' => '2026-09-01T00:00:00Z',
            'to' => '2026-09-30T00:00:00Z',
        ], $this->queryParams($request));
    }

    public function testStatisticsReportWithoutToSendsOnlyFrom(): void
    {
        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode(['roomName' => 'sales-room']))], $history);
        $roomReport = new RoomReport($client);

        $roomReport->statisticsReport('sales-room', '2026-09-01T00:00:00Z');

        $request = $this->lastRequest($history);
        $this->assertSame('from=2026-09-01T00%3A00%3A00Z', $request->getUri()->getQuery());
    }

    public function testStatisticsReportThrowsNotFoundOn404(): void
    {
        $this->expectException(TalkNotFoundException::class);

        $exception = new ClientException(
            'Not found',
            new Request('GET', 'RoomReport/missing-room/statistics/report'),
            new Response(404)
        );

        $client = $this->mockClient([$exception]);
        $roomReport = new RoomReport($client);

        $roomReport->statisticsReport('missing-room', '2026-09-01T00:00:00Z');
    }
}
