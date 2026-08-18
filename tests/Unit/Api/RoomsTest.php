<?php

namespace Kontur\Talk\Tests\Unit\Api;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kontur\Talk\Api\Rooms;
use Kontur\Talk\Exception\TalkNotFoundException;

class RoomsTest extends ApiTestCase
{
    public function testGetCallsCorrectEndpointAndDecodesResponse(): void
    {
        // Форма ответа — TalkRoom из OpenAPI-спеки
        $response = [
            'roomName' => 'sales-room',
            'title' => 'Комната продаж',
            'description' => null,
            'stageConferenceId' => 'stage-1',
            'conferenceId' => 'conf-1',
            'securityType' => 'pinCode',
            'pinCode' => '1234',
            'allowAnonymous' => true,
            'anonymousAccessExpirationDate' => '2026-09-01T00:00:00Z',
            'enableLobby' => false,
            'audioPolicy' => 'none',
            'videoPolicy' => 'none',
            'screenSharePolicy' => 'none',
        ];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $rooms = new Rooms($client);

        $result = $rooms->get('sales-room');

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/Rooms/sales-room', $request->getUri()->getPath());
    }

    public function testGetThrowsNotFoundOn404(): void
    {
        $this->expectException(TalkNotFoundException::class);

        $exception = new ClientException(
            'Not found',
            new Request('GET', 'Rooms/missing-room'),
            new Response(404)
        );

        $client = $this->mockClient([$exception]);
        $rooms = new Rooms($client);

        $rooms->get('missing-room');
    }

    public function testCreateOrUpdateSendsPutWithBodyVerbatim(): void
    {
        $params = [
            'title' => 'Комната продаж',
            'description' => 'Описание',
            'moderatorKeys' => ['user-1', 'user-2'],
            'allowAnonymous' => true,
            'anonymousAccessExpirationDate' => '2026-09-01T00:00:00Z',
            'enableLobby' => true,
            'audioPolicy' => 'none',
            'videoPolicy' => 'muted',
            'screenSharePolicy' => 'disabled',
        ];

        $response = array_merge($params, [
            'roomName' => 'sales-room',
            'stageConferenceId' => 'stage-1',
            'conferenceId' => 'conf-1',
            'securityType' => 'none',
            'pinCode' => null,
        ]);

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $rooms = new Rooms($client);

        $result = $rooms->createOrUpdate('sales-room', $params);

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/api/Rooms/sales-room', $request->getUri()->getPath());
        $this->assertSame($params, $this->jsonBody($request));
    }

    public function testSetPinCodeSendsPinCodeInBody(): void
    {
        $history = [];
        $client = $this->mockClient([new Response(200)], $history);
        $rooms = new Rooms($client);

        $rooms->setPinCode('sales-room', '123456');

        $request = $this->lastRequest($history);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Rooms/sales-room/lock', $request->getUri()->getPath());
        $this->assertSame(['pinCode' => '123456'], $this->jsonBody($request));
    }

    public function testSetPinCodeWithNullClearsPinCode(): void
    {
        $history = [];
        $client = $this->mockClient([new Response(200)], $history);
        $rooms = new Rooms($client);

        $rooms->setPinCode('sales-room', null);

        $request = $this->lastRequest($history);
        $this->assertSame(['pinCode' => null], $this->jsonBody($request));
    }

    public function testEndConferenceCallsCorrectEndpointWithEmptyBody(): void
    {
        $history = [];
        $client = $this->mockClient([new Response(200)], $history);
        $rooms = new Rooms($client);

        $rooms->endConference('sales-room');

        $request = $this->lastRequest($history);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/Rooms/sales-room/endconference', $request->getUri()->getPath());
        $this->assertSame([], $this->jsonBody($request));
    }
}
