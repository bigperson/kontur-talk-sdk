<?php

namespace Kontur\Talk\Tests\Unit\Api;

use GuzzleHttp\Psr7\Response;
use Kontur\Talk\Api\Applications;

class ApplicationsTest extends ApiTestCase
{
    public function testAccessInfoCallsCorrectEndpointAndDecodesScopes(): void
    {
        // Форма ответа — TalkDomainApplicationAccessInfo: {expiredAt, scopes: [{type, restrictionType}]}
        $response = [
            'expiredAt' => '2027-01-01T00:00:00Z',
            'scopes' => [
                ['type' => 'recording', 'restrictionType' => 'readWrite'],
                ['type' => 'calendar', 'restrictionType' => 'readWrite'],
                ['type' => 'rooms', 'restrictionType' => 'read'],
            ],
        ];

        $history = [];
        $client = $this->mockClient([new Response(200, [], json_encode($response))], $history);
        $applications = new Applications($client);

        $result = $applications->accessInfo();

        $this->assertEquals($response, $result);

        $request = $this->lastRequest($history);
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/domain/applications/access-info', $request->getUri()->getPath());
    }
}
