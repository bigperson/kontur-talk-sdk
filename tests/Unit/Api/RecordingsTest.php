<?php

namespace Kontur\Talk\Tests\Unit\Api;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kontur\Talk\Api\Recordings;
use Kontur\Talk\Exception\TalkNotFoundException;

class RecordingsTest extends ApiTestCase
{
    public function testListDomainPassesFiltersThroughAsQuery(): void
    {
        // Форма ответа — TalkPage<TalkDomainConferenceRecording>: {entities[], nextPageToken, prevPageToken}
        $response = [
            'entities' => [
                [
                    'id' => 'rec-1',
                    'key' => 'rec-1',
                    'title' => 'Демо.mp4',
                    'createdDate' => '2026-09-01T10:45:00Z',
                    'roomName' => 'sales-room',
                    'participantsCount' => 2,
                    'size' => 123456,
                    'duration' => 2700,
                    'allowAnonymousAccess' => false,
                    'immutable' => false,
                ],
            ],
            'nextPageToken' => 'next-token',
            'prevPageToken' => null,
        ];

        $filters = [
            'startFrom' => '2026-09-01T00:00:00Z',
            'startTo' => '2026-09-30T00:00:00Z',
            'query' => 'Демо',
            'title' => 'Демо',
            'top' => 20,
            'orderMode' => 'byTimeNewFirst',
        ];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $recordings = new Recordings($client);

        $result = $recordings->listDomain($filters);

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/Domain/recordings/v2', $request->getUri()->getPath());
        $this->assertSame([
            'startFrom' => '2026-09-01T00:00:00Z',
            'startTo' => '2026-09-30T00:00:00Z',
            'query' => 'Демо',
            'title' => 'Демо',
            'top' => '20',
            'orderMode' => 'byTimeNewFirst',
        ], $this->queryParams($request));
    }

    public function testGetDomainCallsCorrectEndpoint(): void
    {
        $response = [
            'id' => 'rec-1',
            'key' => 'rec-1',
            'title' => 'Демо.mp4',
            'createdDate' => '2026-09-01T10:45:00Z',
            'roomName' => 'sales-room',
            'participantsCount' => 2,
            'size' => 123456,
            'duration' => 2700,
            'allowAnonymousAccess' => false,
            'immutable' => false,
        ];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $recordings = new Recordings($client);

        $result = $recordings->getDomain('rec-1');

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/Domain/recordings/rec-1', $request->getUri()->getPath());
    }

    public function testGetDomainThrowsNotFoundOn404(): void
    {
        $this->expectException(TalkNotFoundException::class);

        $exception = new ClientException(
            'Not found',
            new Request('GET', 'Domain/recordings/missing'),
            new Response(404)
        );

        $client = $this->mockClient([$exception]);
        $recordings = new Recordings($client);

        $recordings->getDomain('missing');
    }

    public function testGetAccessCallsCorrectEndpointAndDecodesResponse(): void
    {
        // Форма ответа — RecordsResourceAccessResponse
        $response = [
            'userAccesses' => [
                ['user' => ['key' => 'user-1', 'email' => 'user@example.com'], 'roleId' => 'viewer'],
            ],
            'linkAccess' => ['scope' => 'domain'],
        ];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $recordings = new Recordings($client);

        $result = $recordings->getAccess('rec-1');

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/Recordings/rec-1/access', $request->getUri()->getPath());
    }

    public function testPatchAccessSendsPatchWithLinkScopeAndForceUpdate(): void
    {
        $history = [];
        $client = $this->mockClient([new Response(200)], $history);
        $recordings = new Recordings($client);

        $userAccesses = [['userKey' => 'user-1', 'roleId' => 'viewer']];

        $recordings->patchAccess('rec-1', $userAccesses, 'global', true);

        $request = $this->lastRequest($history);
        $this->assertSame('PATCH', $request->getMethod());
        $this->assertSame('/api/Recordings/rec-1/access', $request->getUri()->getPath());
        $this->assertSame([
            'userAccesses' => $userAccesses,
            'forceUpdate' => true,
            'linkAccess' => ['scope' => 'global'],
        ], $this->jsonBody($request));
    }

    public function testPatchAccessOmitsLinkAccessWhenScopeNotProvided(): void
    {
        $history = [];
        $client = $this->mockClient([new Response(200)], $history);
        $recordings = new Recordings($client);

        $userAccesses = [['userKey' => 'user-1', 'roleId' => 'viewer']];

        $recordings->patchAccess('rec-1', $userAccesses);

        $request = $this->lastRequest($history);
        $this->assertSame([
            'userAccesses' => $userAccesses,
            'forceUpdate' => false,
        ], $this->jsonBody($request));
        $this->assertArrayNotHasKey('linkAccess', $this->jsonBody($request));
    }

    public function testPatchAccessRejectsInvalidLinkScopeBeforeRequest(): void
    {
        $this->expectException(\ValueError::class);

        // Пустая очередь ответов: если бы запрос всё же ушёл, MockHandler бросил бы
        // OutOfBoundsException, а не ValueError — тест различает эти два случая.
        $client = $this->mockClient([]);
        $recordings = new Recordings($client);

        $recordings->patchAccess('rec-1', [], 'not-a-real-scope');
    }

    public function testTranscriptCallsCorrectEndpointAndDecodesResponse(): void
    {
        // Форма ответа — TalkTranscript
        $response = [
            'status' => 'complete',
            'statusMessage' => null,
            'transcriptId' => '11111111-1111-1111-1111-111111111111',
            'tracks' => [
                [
                    'trackId' => 'track-1',
                    'speaker' => ['userInfo' => ['key' => 'user-1']],
                    'chunks' => [
                        [
                            'chunkId' => 'chunk-1',
                            'startTimeOffsetInMillis' => 0,
                            'endTimeOffsetInMillis' => 1500,
                            'text' => 'Добрый день',
                            'words' => [],
                            'confidence' => 0.98,
                        ],
                    ],
                ],
            ],
            'errors' => [],
        ];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $recordings = new Recordings($client);

        $result = $recordings->transcript('rec-1');

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/recordings/rec-1/transcript', $request->getUri()->getPath());
    }

    public function testSummaryValidatesTypeAndCallsCorrectEndpoint(): void
    {
        // Форма ответа — TalkSummaryV2Result
        $response = [
            'summaryId' => '22222222-2222-2222-2222-222222222222',
            'status' => 'success',
            'startedAt' => '2026-09-01T10:45:00Z',
            'chunks' => [
                ['type' => 'summary', 'timestamp' => 0, 'version' => 1, 'text' => 'Краткое содержание'],
            ],
            'hidden' => false,
        ];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $recordings = new Recordings($client);

        $result = $recordings->summary('rec-1', 'shortSummary');

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/recordings/rec-1/summary/shortSummary', $request->getUri()->getPath());
    }

    public function testSummaryRejectsInvalidTypeBeforeRequest(): void
    {
        $this->expectException(\ValueError::class);

        $client = $this->mockClient([]);
        $recordings = new Recordings($client);

        $recordings->summary('rec-1', 'bogusType');
    }

    public function testCompositeCallsV2SummaryEndpointAndDecodesResponse(): void
    {
        // Форма ответа — TalkCompositeSpeechCoreResult: {transcriptionV2, shortSummaryV2, protocolV2}
        $response = [
            'transcriptionV2' => ['status' => 'success', 'statusMessage' => '', 'tracks' => [], 'errors' => []],
            'shortSummaryV2' => ['summaryId' => null, 'status' => 'success', 'chunks' => [], 'hidden' => false],
            'protocolV2' => ['summaryId' => null, 'status' => 'notFound', 'chunks' => [], 'hidden' => false],
        ];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $recordings = new Recordings($client);

        $result = $recordings->composite('rec-1');

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/recordings/v2/rec-1/summary', $request->getUri()->getPath());
    }
}
