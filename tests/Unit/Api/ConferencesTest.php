<?php

namespace Kontur\Talk\Tests\Unit\Api;

use DateTime;
use Kontur\Talk\Api\Conferences;
use Kontur\Talk\TalkClient;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class ConferencesTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    
    private TalkClient $clientMock;
    private Conferences $conferencesApi;
    
    protected function setUp(): void
    {
        $this->clientMock = Mockery::mock(TalkClient::class);
        $this->conferencesApi = new Conferences($this->clientMock);
    }
    
    public function testGetAllWithDefaultParameters(): void
    {
        $expectedResponse = [
            'conferences' => [
                [
                    'conferenceId' => '1234567890abcdef',
                    'title' => 'Test Conference',
                    'creationTime' => '2023-06-01T12:00:00Z'
                ]
            ],
            'offset' => 'next-page-token'
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('conferences', [
                'top' => 100
            ])
            ->andReturn($expectedResponse);
        
        $result = $this->conferencesApi->getAll();
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetAllWithCustomParameters(): void
    {
        $expectedResponse = [
            'conferences' => [
                [
                    'conferenceId' => '1234567890abcdef',
                    'title' => 'Test Conference',
                    'creationTime' => '2023-06-01T12:00:00Z'
                ]
            ],
            'offset' => 'next-page-token'
        ];
        
        $startTime = new DateTime('2023-05-01T00:00:00Z');
        $endTime = new DateTime('2023-06-01T00:00:00Z');
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('conferences', [
                'top' => 50,
                'offset' => 'page-token',
                'startTime' => $startTime->format('Y-m-d\TH:i:s.v\Z'),
                'endTime' => $endTime->format('Y-m-d\TH:i:s.v\Z'),
                'title' => 'Test',
                'participantEmail' => 'test@example.com',
                'participantLogin' => 'testuser',
                'participantDisplayName' => 'Test User'
            ])
            ->andReturn($expectedResponse);
        
        $result = $this->conferencesApi->getAll(
            50,
            'page-token',
            $startTime,
            $endTime,
            'Test',
            'test@example.com',
            'testuser',
            'Test User'
        );
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetByIdCallsCorrectEndpoint(): void
    {
        $conferenceId = '1234567890abcdef';
        $expectedResponse = [
            'conferenceId' => $conferenceId,
            'title' => 'Test Conference',
            'creationTime' => '2023-06-01T12:00:00Z'
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with("conferences/{$conferenceId}")
            ->andReturn($expectedResponse);
        
        $result = $this->conferencesApi->getById($conferenceId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testCreateReturnsCreatedConference(): void
    {
        $conferenceData = [
            'title' => 'New Conference',
            'isPrivate' => true,
            'timezone' => 'Europe/Moscow',
            'autorecord' => true
        ];
        
        $expectedResponse = [
            'conferenceId' => '0987654321fedcba',
            'title' => 'New Conference',
            'isPrivate' => true,
            'timezone' => 'Europe/Moscow',
            'autorecord' => true,
            'creationTime' => '2023-06-01T12:00:00Z'
        ];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with('conferences', $conferenceData)
            ->andReturn($expectedResponse);
        
        $result = $this->conferencesApi->create($conferenceData);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testUpdateCallsCorrectEndpoint(): void
    {
        $conferenceId = '1234567890abcdef';
        $updateData = [
            'title' => 'Updated Conference',
            'isPrivate' => false
        ];
        
        $expectedResponse = [
            'conferenceId' => $conferenceId,
            'title' => 'Updated Conference',
            'isPrivate' => false,
            'creationTime' => '2023-06-01T12:00:00Z'
        ];
        
        $this->clientMock->shouldReceive('put')
            ->once()
            ->with("conferences/{$conferenceId}", $updateData)
            ->andReturn($expectedResponse);
        
        $result = $this->conferencesApi->update($conferenceId, $updateData);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testDeleteCallsCorrectEndpoint(): void
    {
        $conferenceId = '1234567890abcdef';
        $expectedResponse = [];
        
        $this->clientMock->shouldReceive('delete')
            ->once()
            ->with("conferences/{$conferenceId}")
            ->andReturn($expectedResponse);
        
        $result = $this->conferencesApi->delete($conferenceId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testStartCallsCorrectEndpoint(): void
    {
        $conferenceId = '1234567890abcdef';
        $expectedResponse = [
            'conferenceId' => $conferenceId,
            'status' => 'started',
            'joinUrl' => 'https://example.com/join/conference'
        ];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with("conferences/{$conferenceId}/start")
            ->andReturn($expectedResponse);
        
        $result = $this->conferencesApi->start($conferenceId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testStopCallsCorrectEndpoint(): void
    {
        $conferenceId = '1234567890abcdef';
        $expectedResponse = [
            'conferenceId' => $conferenceId,
            'status' => 'stopped'
        ];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with("conferences/{$conferenceId}/stop")
            ->andReturn($expectedResponse);
        
        $result = $this->conferencesApi->stop($conferenceId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetParticipantsCallsCorrectEndpoint(): void
    {
        $conferenceId = '1234567890abcdef';
        $expectedResponse = [
            'participants' => [
                [
                    'userId' => '123456',
                    'displayName' => 'Test User',
                    'email' => 'test@example.com',
                    'role' => 'presenter'
                ]
            ]
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with("conferences/{$conferenceId}/participants")
            ->andReturn($expectedResponse);
        
        $result = $this->conferencesApi->getParticipants($conferenceId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testAddParticipantsCallsCorrectEndpoint(): void
    {
        $conferenceId = '1234567890abcdef';
        $participants = [
            [
                'email' => 'user1@example.com',
                'role' => 'presenter'
            ],
            [
                'email' => 'user2@example.com',
                'role' => 'attendee'
            ]
        ];
        
        $expectedResponse = [
            'participants' => [
                [
                    'userId' => '123456',
                    'displayName' => 'User 1',
                    'email' => 'user1@example.com',
                    'role' => 'presenter'
                ],
                [
                    'userId' => '789012',
                    'displayName' => 'User 2',
                    'email' => 'user2@example.com',
                    'role' => 'attendee'
                ]
            ]
        ];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with("conferences/{$conferenceId}/participants", ['participants' => $participants])
            ->andReturn($expectedResponse);
        
        $result = $this->conferencesApi->addParticipants($conferenceId, $participants);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testUpdateParticipantCallsCorrectEndpoint(): void
    {
        $conferenceId = '1234567890abcdef';
        $userId = '123456';
        $updateData = [
            'role' => 'presenter'
        ];
        
        $expectedResponse = [
            'userId' => $userId,
            'displayName' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'presenter'
        ];
        
        $this->clientMock->shouldReceive('put')
            ->once()
            ->with("conferences/{$conferenceId}/participants/{$userId}", $updateData)
            ->andReturn($expectedResponse);
        
        $result = $this->conferencesApi->updateParticipant($conferenceId, $userId, $updateData);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testRemoveParticipantCallsCorrectEndpoint(): void
    {
        $conferenceId = '1234567890abcdef';
        $userId = '123456';
        $expectedResponse = [];
        
        $this->clientMock->shouldReceive('delete')
            ->once()
            ->with("conferences/{$conferenceId}/participants/{$userId}")
            ->andReturn($expectedResponse);
        
        $result = $this->conferencesApi->removeParticipant($conferenceId, $userId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetRecordingsCallsCorrectEndpoint(): void
    {
        $conferenceId = '1234567890abcdef';
        $expectedResponse = [
            'recordings' => [
                [
                    'recordingId' => '5678901234fedcba',
                    'name' => 'Recording 1',
                    'duration' => 3600,
                    'creationTime' => '2023-06-01T12:00:00Z'
                ]
            ]
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with("conferences/{$conferenceId}/recordings")
            ->andReturn($expectedResponse);
        
        $result = $this->conferencesApi->getRecordings($conferenceId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testStartRecordingCallsCorrectEndpoint(): void
    {
        $conferenceId = '1234567890abcdef';
        $expectedResponse = [
            'recordingId' => '5678901234fedcba',
            'status' => 'recording'
        ];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with("conferences/{$conferenceId}/recordings/start")
            ->andReturn($expectedResponse);
        
        $result = $this->conferencesApi->startRecording($conferenceId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testStopRecordingCallsCorrectEndpoint(): void
    {
        $conferenceId = '1234567890abcdef';
        $expectedResponse = [
            'recordingId' => '5678901234fedcba',
            'status' => 'stopped'
        ];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with("conferences/{$conferenceId}/recordings/stop")
            ->andReturn($expectedResponse);
        
        $result = $this->conferencesApi->stopRecording($conferenceId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGenerateJoinLinkCallsCorrectEndpoint(): void
    {
        $conferenceId = '1234567890abcdef';
        $options = [
            'userId' => '123456',
            'displayName' => 'Test User',
            'role' => 'presenter'
        ];
        
        $expectedResponse = [
            'joinUrl' => 'https://example.com/join/conference/token123456'
        ];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with("conferences/{$conferenceId}/joinLink", $options)
            ->andReturn($expectedResponse);
        
        $result = $this->conferencesApi->generateJoinLink($conferenceId, $options);
        
        $this->assertEquals($expectedResponse, $result);
    }
} 