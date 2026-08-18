<?php

namespace Kontur\Talk\Tests\Unit\Api;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Kontur\Talk\TalkClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use ReflectionClass;

/**
 * Общий стенд для тестов Api\*: реальный TalkClient поверх Guzzle MockHandler,
 * с историей отправленных запросов для проверки метода/пути/query/тела.
 */
abstract class ApiTestCase extends TestCase
{
    protected const SPACE = 'testspace';

    /**
     * @param array $responses Очередь ответов/исключений Guzzle
     * @param array $history Заполняется по ссылке: каждый элемент содержит ключ 'request' (RequestInterface)
     */
    protected function mockClient(array $responses, array &$history = []): TalkClient
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($history));

        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $reflection = new ReflectionClass(TalkClient::class);
        $client = $reflection->newInstanceWithoutConstructor();

        $baseUrlProp = $reflection->getProperty('baseUrl');
        $baseUrlProp->setAccessible(true);
        $baseUrlProp->setValue($client, 'https://' . self::SPACE . '.ktalk.ru/api');

        $httpClientProp = $reflection->getProperty('httpClient');
        $httpClientProp->setAccessible(true);
        $httpClientProp->setValue($client, $httpClient);

        return $client;
    }

    /**
     * @param array $history История из mockClient()
     */
    protected function lastRequest(array $history): RequestInterface
    {
        $this->assertNotEmpty($history, 'Ожидался хотя бы один отправленный запрос');

        return $history[count($history) - 1]['request'];
    }

    /**
     * Разбирает query-строку запроса в ассоциативный массив.
     *
     * TalkClient сам сериализует query (см. `TalkClient::buildQuery()`) в формате, объявленном
     * спецификацией для повторяющихся параметров: голый повтор ключа (`roomName=a&roomName=b`),
     * а не индексные скобки (`roomName[0]=a`) и не пустые скобки (`roomName[]=a`). Для разбора
     * такой строки нужен `GuzzleHttp\Psr7\Query::parse()` — она аккумулирует повторяющиеся голые
     * ключи в массив; нативная `parse_str()` для этого не подходит: без скобок в ключе она
     * оставляет только последнее значение (`roomName=a&roomName=b` → `['roomName' => 'b']`).
     */
    protected function queryParams(RequestInterface $request): array
    {
        return \GuzzleHttp\Psr7\Query::parse($request->getUri()->getQuery());
    }

    /**
     * Декодирует JSON-тело запроса в массив
     */
    protected function jsonBody(RequestInterface $request): array
    {
        $body = (string) $request->getBody();

        return $body === '' ? [] : json_decode($body, true);
    }
}
