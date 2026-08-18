<?php

namespace Kontur\Talk\Tests\Unit\Api;

use GuzzleHttp\Psr7\Response;
use Kontur\Talk\Api\Users;

class UsersTest extends ApiTestCase
{
    private function sampleUser(): array
    {
        // Форма ответа — TalkUser из OpenAPI-спеки (только поля, которые использует потребитель SDK)
        return [
            'key' => 'user-1',
            'email' => 'user@example.com',
            'firstname' => 'Иван',
            'surname' => 'Иванов',
            'disabled' => false,
            'userType' => 'normal',
        ];
    }

    public function testScanWithDefaultParameters(): void
    {
        $response = ['users' => [$this->sampleUser()], 'offset' => null];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $users = new Users($client);

        $result = $users->scan();

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/Users/scan', $request->getUri()->getPath());
        $this->assertSame([
            'includeDisabled' => 'false',
            'includeGuests' => 'false',
        ], $this->queryParams($request));
    }

    public function testScanWithCustomParameters(): void
    {
        $response = ['users' => [], 'offset' => 'next-page-key'];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $users = new Users($client);

        $result = $users->scan('page-key', 50, true, true);

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame([
            'includeDisabled' => 'true',
            'includeGuests' => 'true',
            'offset' => 'page-key',
            'top' => '50',
        ], $this->queryParams($request));
    }

    public function testSearchPassesFiltersThroughAsQueryIncludingRepeatedEmail(): void
    {
        $response = ['users' => [$this->sampleUser()]];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $users = new Users($client);

        $filters = [
            'query' => 'Иван',
            'email' => ['user1@example.com', 'user2@example.com'],
            'role' => 'admin',
            'top' => 50,
            'skip' => 10,
        ];

        $result = $users->search($filters);

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/Users', $request->getUri()->getPath());
        $this->assertSame([
            'query' => 'Иван',
            'email' => ['user1@example.com', 'user2@example.com'],
            'role' => 'admin',
            'top' => '50',
            'skip' => '10',
        ], $this->queryParams($request));
    }

    public function testSearchWithNoFiltersSendsNoQuery(): void
    {
        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode(['users' => []]))], $history);
        $users = new Users($client);

        $users->search();

        $request = $this->lastRequest($history);
        $this->assertSame('', $request->getUri()->getQuery());
    }

    public function testGetByKeyCallsCorrectEndpoint(): void
    {
        $response = $this->sampleUser();

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $users = new Users($client);

        $result = $users->getByKey('user-1');

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/Users/user-1', $request->getUri()->getPath());
    }
}
