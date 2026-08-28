<?php

namespace Kontur\Talk\Tests\Unit\Api;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kontur\Talk\Api\Webhooks;
use Kontur\Talk\Exception\TalkNotFoundException;
use Kontur\Talk\Exception\TalkRateLimitException;

class WebhooksTest extends ApiTestCase
{
    public function testGetListCallsCorrectEndpointAndDecodesResponse(): void
    {
        // Форма ответа — массив TalkWebhook из OpenAPI-спеки
        $response = [
            [
                'webhookKey' => 'hook-1',
                'title' => 'Записи в CRM',
                'activated' => true,
                'events' => ['recordingCompleted', 'transcriptionReady'],
                'url' => 'https://example.com/talk/webhook',
                'customHeaders' => [['name' => 'X-Project-Token', 'value' => 'secret']],
                'created' => '2026-09-01T10:00:00Z',
            ],
        ];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $webhooks = new Webhooks($client);

        $result = $webhooks->getList();

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/Webhooks', $request->getUri()->getPath());
        $this->assertSame('', $request->getUri()->getQuery());
    }

    public function testCreateSendsPostWithBodyVerbatim(): void
    {
        $data = [
            'title' => 'Записи в CRM',
            'url' => 'https://example.com/talk/webhook',
            'events' => ['recordingCompleted', 'conferencesFinished'],
            'customHeaders' => [['name' => 'X-Project-Token', 'value' => 'secret']],
        ];

        // Созданный вебхук приходит ещё не активированным — активация асинхронная
        $response = array_merge($data, [
            'webhookKey' => 'hook-1',
            'activated' => false,
            'created' => '2026-09-01T10:00:00Z',
        ]);

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $webhooks = new Webhooks($client);

        $result = $webhooks->create($data);

        $this->assertEquals($response, $result);
        $this->assertFalse($result['activated']);

        $request = $this->lastRequest($history);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Webhooks', $request->getUri()->getPath());
        $this->assertSame($data, $this->jsonBody($request));
    }

    public function testCreateWithoutEventsSendsBodyAsIs(): void
    {
        $data = [
            'title' => 'Записи в CRM',
            'url' => 'https://example.com/talk/webhook',
        ];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode(['webhookKey' => 'hook-1']))], $history);
        $webhooks = new Webhooks($client);

        $webhooks->create($data);

        $this->assertSame($data, $this->jsonBody($this->lastRequest($history)));
    }

    public function testCreateRejectsUnknownEventBeforeRequest(): void
    {
        $history = [];
        $client = $this->mockClient([new Response(200)], $history);
        $webhooks = new Webhooks($client);

        try {
            $webhooks->create([
                'title' => 'Записи в CRM',
                'url' => 'https://example.com/talk/webhook',
                'events' => ['recordingCompleted', 'somethingElse'],
            ]);
            $this->fail('Ожидался \ValueError на недопустимом типе события');
        } catch (\ValueError) {
            // Значение отвергается до отправки — запроса быть не должно
            $this->assertSame([], $history);
        }
    }

    public function testCreateThrowsRateLimitOn429(): void
    {
        // Спецификация ограничивает создание вебхуков: 10 запросов в сутки на пространство
        $this->expectException(TalkRateLimitException::class);

        $exception = new ClientException(
            'Too many requests',
            new Request('POST', 'Webhooks'),
            new Response(429)
        );

        $client = $this->mockClient([$exception]);
        $webhooks = new Webhooks($client);

        $webhooks->create([
            'title' => 'Записи в CRM',
            'url' => 'https://example.com/talk/webhook',
        ]);
    }

    public function testActivateSendsActivationKeyInBody(): void
    {
        $history = [];
        $client = $this->mockClient([new Response(200)], $history);
        $webhooks = new Webhooks($client);

        $webhooks->activate('hook-1', ['activationKey' => 'activation-key']);

        $request = $this->lastRequest($history);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Webhooks/hook-1/activate', $request->getUri()->getPath());
        $this->assertSame(['activationKey' => 'activation-key'], $this->jsonBody($request));
    }

    public function testDeleteCallsCorrectEndpoint(): void
    {
        $history = [];
        $client = $this->mockClient([new Response(200)], $history);
        $webhooks = new Webhooks($client);

        $webhooks->delete('hook-1');

        $request = $this->lastRequest($history);
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/api/Webhooks/hook-1', $request->getUri()->getPath());
    }

    public function testDeleteThrowsNotFoundOn404(): void
    {
        $this->expectException(TalkNotFoundException::class);

        $exception = new ClientException(
            'Not found',
            new Request('DELETE', 'Webhooks/missing-hook'),
            new Response(404)
        );

        $client = $this->mockClient([$exception]);
        $webhooks = new Webhooks($client);

        $webhooks->delete('missing-hook');
    }
}
