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
     * TalkClient передаёт массивы параметров в Guzzle как есть, а Guzzle сериализует их через
     * встроенный http_build_query() — то есть повторяющиеся значения на проводе кодируются
     * PHP-нотацией с индексами (`roomName[0]=a&roomName[1]=b`), а не голым повтором ключа
     * (`roomName=a&roomName=b`) и не пустыми скобками (`roomName[]=a`). Поэтому для разбора
     * используется parse_str() — она понимает именно эту нотацию и восстанавливает массив.
     */
    protected function queryParams(RequestInterface $request): array
    {
        parse_str($request->getUri()->getQuery(), $result);

        return $result;
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
