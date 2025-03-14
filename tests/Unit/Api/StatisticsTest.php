<?php

namespace Kontur\Talk\Tests\Unit\Api;

use DateTime;
use Kontur\Talk\Api\Statistics;
use Kontur\Talk\TalkClient;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class StatisticsTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    
    private TalkClient $clientMock;
    private Statistics $statisticsApi;
    
    protected function setUp(): void
    {
        $this->clientMock = Mockery::mock(TalkClient::class);
        $this->statisticsApi = new Statistics($this->clientMock);
    }
    
    public function testGetOnlineCallsCorrectEndpoint(): void
    {
        $expectedResponse = [
            'usersCount' => 10,
            'conferencesCount' => 3
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('Domain/stats/online')
            ->andReturn($expectedResponse);
        
        $result = $this->statisticsApi->getOnline();
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetRegisteredUsersCallsCorrectEndpoint(): void
    {
        $expectedResponse = [
            'endDate' => '2023-06-01T12:00:00Z',
            'statistics' => [
                'totalRegisteredUsers' => 100,
                'totalRegisteredInactiveUsers' => 10
            ]
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('domain/statisticsTotal')
            ->andReturn($expectedResponse);
        
        $result = $this->statisticsApi->getRegisteredUsers();
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetActiveUsersWithDefaultParameters(): void
    {
        $expectedResponse = [
            'startDate' => '2023-05-01T00:00:00Z',
            'endDate' => '2023-06-01T00:00:00Z',
            'statistics' => [
                'anonymousUsers' => 50,
                'registeredUsers' => 75
            ]
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('domain/statistics', [])
            ->andReturn($expectedResponse);
        
        $result = $this->statisticsApi->getActiveUsers();
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetActiveUsersWithCustomParameters(): void
    {
        $start = new DateTime('2023-05-01T00:00:00Z');
        $end = new DateTime('2023-06-01T00:00:00Z');
        
        $expectedResponse = [
            'startDate' => '2023-05-01T00:00:00Z',
            'endDate' => '2023-06-01T00:00:00Z',
            'statistics' => [
                'anonymousUsers' => 50,
                'registeredUsers' => 75
            ]
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('domain/statistics', [
                'start' => $start->format('Y-m-d\TH:i:s.v\Z'),
                'end' => $end->format('Y-m-d\TH:i:s.v\Z')
            ])
            ->andReturn($expectedResponse);
        
        $result = $this->statisticsApi->getActiveUsers($start, $end);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetConferencesWithDefaultParameters(): void
    {
        $expectedResponse = [
            'startDate' => '2023-05-01T00:00:00Z',
            'endDate' => '2023-06-01T00:00:00Z',
            'statistics' => [
                'conferencesCount' => 84,
                'conferencesInternalUsersDurationMin' => 36,
                'conferencesExternalUsersDurationMin' => 8
            ]
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('Domain/stats/conferences', [])
            ->andReturn($expectedResponse);
        
        $result = $this->statisticsApi->getConferences();
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetConferencesWithCustomParameters(): void
    {
        $fromDate = new DateTime('2023-05-01T00:00:00Z');
        $toDate = new DateTime('2023-06-01T00:00:00Z');
        
        $expectedResponse = [
            'startDate' => '2023-05-01T00:00:00Z',
            'endDate' => '2023-06-01T00:00:00Z',
            'statistics' => [
                'conferencesCount' => 84,
                'conferencesInternalUsersDurationMin' => 36,
                'conferencesExternalUsersDurationMin' => 8
            ]
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('Domain/stats/conferences', [
                'fromDate' => $fromDate->format('Y-m-d\TH:i:s.v\Z'),
                'toDate' => $toDate->format('Y-m-d\TH:i:s.v\Z')
            ])
            ->andReturn($expectedResponse);
        
        $result = $this->statisticsApi->getConferences($fromDate, $toDate);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetKiosksWithDefaultParameters(): void
    {
        $expectedResponse = [
            'domainId' => '5fa4d111bf7f111f784f1111',
            'created' => '2023-11-13T12:18:45.0386443Z',
            'statistics' => [
                [
                    'id' => '645678595d190c01e34f0099',
                    'title' => 'Киоск 1',
                    'conferencesCount' => 1,
                    'conferencesMinutes' => 4
                ]
            ]
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('Domain/stats/kiosks', [])
            ->andReturn($expectedResponse);
        
        $result = $this->statisticsApi->getKiosks();
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetKiosksWithCustomParameters(): void
    {
        $start = new DateTime('2023-05-01T00:00:00Z');
        $end = new DateTime('2023-06-01T00:00:00Z');
        
        $expectedResponse = [
            'domainId' => '5fa4d111bf7f111f784f1111',
            'created' => '2023-11-13T12:18:45.0386443Z',
            'statistics' => [
                [
                    'id' => '645678595d190c01e34f0099',
                    'title' => 'Киоск 1',
                    'conferencesCount' => 1,
                    'conferencesMinutes' => 4
                ]
            ]
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('Domain/stats/kiosks', [
                'start' => $start->format('Y-m-d\TH:i:s.v\Z'),
                'end' => $end->format('Y-m-d\TH:i:s.v\Z')
            ])
            ->andReturn($expectedResponse);
        
        $result = $this->statisticsApi->getKiosks($start, $end);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetKiosksOnlineCallsCorrectEndpoint(): void
    {
        $expectedResponse = [
            'totalKioskConferencesCount' => 5
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('Domain/stats/kiosks/online')
            ->andReturn($expectedResponse);
        
        $result = $this->statisticsApi->getKiosksOnline();
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetActiveRecordingsCallsCorrectEndpoint(): void
    {
        $expectedResponse = [
            'totalActiveRecordings' => 3
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('Domain/stats/recordings/online')
            ->andReturn($expectedResponse);
        
        $result = $this->statisticsApi->getActiveRecordings();
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetRecordingsTotalSizeCallsCorrectEndpoint(): void
    {
        $expectedResponse = [
            'totalRecordingsSizeMb' => 1024
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('domain/stats/recordings/totalSize')
            ->andReturn($expectedResponse);
        
        $result = $this->statisticsApi->getRecordingsTotalSize();
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetStreamsOnlineCallsCorrectEndpoint(): void
    {
        $expectedResponse = [
            'activeStreams' => 2,
            'activeStreamsViewers' => 50
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('Domain/stats/streams/online')
            ->andReturn($expectedResponse);
        
        $result = $this->statisticsApi->getStreamsOnline();
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetTariffExpirationDateCallsCorrectEndpoint(): void
    {
        $expectedResponse = [
            'expirationDate' => '2023-12-31T23:59:59Z'
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('domain/stats/tariffExpirationDate')
            ->andReturn($expectedResponse);
        
        $result = $this->statisticsApi->getTariffExpirationDate();
        
        $this->assertEquals($expectedResponse, $result);
    }
} 