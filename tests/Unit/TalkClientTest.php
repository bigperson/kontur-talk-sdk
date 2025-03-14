<?php

namespace Kontur\Talk\Tests\Unit;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkNotFoundException;
use Kontur\Talk\Exception\TalkRateLimitException;
use Kontur\Talk\TalkClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;

class TalkClientTest extends TestCase
{
    private const SPACE = 'testspace';
    private const API_KEY = 'test-api-key';

    public function testConstructSetsBaseUrlAndApiKey(): void
    {
        $client = new TalkClient(self::SPACE, self::API_KEY);

        $reflection = new ReflectionClass($client);

        $baseUrlProp = $reflection->getProperty('baseUrl');
        $baseUrlProp->setAccessible(true);
        $baseUrl = $baseUrlProp->getValue($client);

        $apiKeyProp = $reflection->getProperty('apiKey');
        $apiKeyProp->setAccessible(true);
        $apiKey = $apiKeyProp->getValue($client);

        $this->assertEquals("https://" . self::SPACE . ".ktalk.ru/api", $baseUrl);
        $this->assertEquals(self::API_KEY, $apiKey);
    }

    public function testConstructInitializesHttpClient(): void
    {
        $client = new TalkClient(self::SPACE, self::API_KEY);

        $httpClient = $client->getHttpClient();

        $this->assertInstanceOf(HttpClient::class, $httpClient);
    }

    public function testGetReturnsResponseData(): void
    {
        $expectedData = ['key' => 'value'];
        $mockClient = $this->createMockClient([
            new Response(200, [], json_encode($expectedData))
        ]);

        $result = $mockClient->get('test-endpoint', ['param' => 'value']);

        $this->assertEquals($expectedData, $result);
    }

    public function testPostReturnsResponseData(): void
    {
        $expectedData = ['status' => 'success'];
        $mockClient = $this->createMockClient([
            new Response(200, [], json_encode($expectedData))
        ]);

        $result = $mockClient->post('test-endpoint', ['data' => 'value'], ['param' => 'value']);

        $this->assertEquals($expectedData, $result);
    }

    public function testPutReturnsResponseData(): void
    {
        $expectedData = ['status' => 'updated'];
        $mockClient = $this->createMockClient([
            new Response(200, [], json_encode($expectedData))
        ]);

        $result = $mockClient->put('test-endpoint', ['data' => 'value'], ['param' => 'value']);

        $this->assertEquals($expectedData, $result);
    }

    public function testDeleteReturnsResponseData(): void
    {
        $expectedData = ['status' => 'deleted'];
        $mockClient = $this->createMockClient([
            new Response(200, [], json_encode($expectedData))
        ]);

        $result = $mockClient->delete('test-endpoint', ['param' => 'value']);

        $this->assertEquals($expectedData, $result);
    }

    public function testEmptyResponseReturnsEmptyArray(): void
    {
        $mockClient = $this->createMockClient([
            new Response(204)
        ]);

        $result = $mockClient->get('test-endpoint');

        $this->assertEquals([], $result);
    }

    public function testThrowsTalkRateLimitExceptionOn429(): void
    {
        $this->expectException(TalkRateLimitException::class);

        $exception = new ClientException(
            'Rate limit exceeded',
            new Request('GET', 'test-endpoint'),
            new Response(429)
        );

        $mockClient = $this->createMockClient([$exception]);

        $mockClient->get('test-endpoint');
    }

    public function testThrowsTalkNotFoundExceptionOn404(): void
    {
        $this->expectException(TalkNotFoundException::class);

        $exception = new ClientException(
            'Not found',
            new Request('GET', 'test-endpoint'),
            new Response(404)
        );

        $mockClient = $this->createMockClient([$exception]);

        $mockClient->get('test-endpoint');
    }

    public function testThrowsTalkApiExceptionOnClientError(): void
    {
        $this->expectException(TalkApiException::class);

        $responseBody = json_encode(['errorMessage' => 'Bad request']);
        $exception = new ClientException(
            'Bad request',
            new Request('GET', 'test-endpoint'),
            new Response(400, [], $responseBody)
        );

        $mockClient = $this->createMockClient([$exception]);

        $mockClient->get('test-endpoint');
    }

    public function testThrowsTalkClientExceptionOnServerError(): void
    {
        $this->expectException(TalkClientException::class);

        $exception = new ServerException(
            'Server error',
            new Request('GET', 'test-endpoint'),
            new Response(500)
        );

        $mockClient = $this->createMockClient([$exception]);

        $mockClient->get('test-endpoint');
    }

    private function createMockClient(array $responses): TalkClient
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $reflection = new ReflectionClass(TalkClient::class);
        $client = $reflection->newInstanceWithoutConstructor();

        $baseUrlProp = $reflection->getProperty('baseUrl');
        $baseUrlProp->setAccessible(true);
        $baseUrlProp->setValue($client, "https://" . self::SPACE . ".ktalk.ru/api");

        $apiKeyProp = $reflection->getProperty('apiKey');
        $apiKeyProp->setAccessible(true);
        $apiKeyProp->setValue($client, self::API_KEY);

        $httpClientProp = $reflection->getProperty('httpClient');
        $httpClientProp->setAccessible(true);
        $httpClientProp->setValue($client, $httpClient);

        // Initialize API instances - optional for this test as we don't use them

        return $client;
    }
}
