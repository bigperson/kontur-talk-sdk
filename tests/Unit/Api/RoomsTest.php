<?php

namespace Kontur\Talk\Tests\Unit\Api;

use DateTime;
use Kontur\Talk\Api\Rooms;
use Kontur\Talk\TalkClient;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class RoomsTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    
    private TalkClient $clientMock;
    private Rooms $roomsApi;
    
    protected function setUp(): void
    {
        $this->clientMock = Mockery::mock(TalkClient::class);
        $this->roomsApi = new Rooms($this->clientMock);
    }
    
    public function testGetAllWithDefaultParameters(): void
    {
        $expectedResponse = [
            'rooms' => [
                [
                    'roomId' => '1234567890abcdef',
                    'title' => 'Test Room',
                    'creationTime' => '2023-06-01T12:00:00Z'
                ]
            ],
            'offset' => 'next-page-token'
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('rooms', [
                'top' => 100
            ])
            ->andReturn($expectedResponse);
        
        $result = $this->roomsApi->getAll();
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetAllWithCustomParameters(): void
    {
        $expectedResponse = [
            'rooms' => [
                [
                    'roomId' => '1234567890abcdef',
                    'title' => 'Test Room',
                    'creationTime' => '2023-06-01T12:00:00Z'
                ]
            ],
            'offset' => 'next-page-token'
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('rooms', [
                'top' => 50,
                'offset' => 'page-token',
                'title' => 'Test'
            ])
            ->andReturn($expectedResponse);
        
        $result = $this->roomsApi->getAll(50, 'page-token', 'Test');
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetByIdCallsCorrectEndpoint(): void
    {
        $roomId = '1234567890abcdef';
        $expectedResponse = [
            'roomId' => $roomId,
            'title' => 'Test Room',
            'creationTime' => '2023-06-01T12:00:00Z'
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with("rooms/{$roomId}")
            ->andReturn($expectedResponse);
        
        $result = $this->roomsApi->getById($roomId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testCreateReturnsCreatedRoom(): void
    {
        $roomData = [
            'title' => 'New Room',
            'isPrivate' => true,
            'timezone' => 'Europe/Moscow',
        ];
        
        $expectedResponse = [
            'roomId' => '0987654321fedcba',
            'title' => 'New Room',
            'isPrivate' => true,
            'timezone' => 'Europe/Moscow',
            'creationTime' => '2023-06-01T12:00:00Z'
        ];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with('rooms', $roomData)
            ->andReturn($expectedResponse);
        
        $result = $this->roomsApi->create($roomData);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testUpdateCallsCorrectEndpoint(): void
    {
        $roomId = '1234567890abcdef';
        $updateData = [
            'title' => 'Updated Room',
            'isPrivate' => false
        ];
        
        $expectedResponse = [
            'roomId' => $roomId,
            'title' => 'Updated Room',
            'isPrivate' => false,
            'creationTime' => '2023-06-01T12:00:00Z'
        ];
        
        $this->clientMock->shouldReceive('put')
            ->once()
            ->with("rooms/{$roomId}", $updateData)
            ->andReturn($expectedResponse);
        
        $result = $this->roomsApi->update($roomId, $updateData);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testDeleteCallsCorrectEndpoint(): void
    {
        $roomId = '1234567890abcdef';
        $expectedResponse = [];
        
        $this->clientMock->shouldReceive('delete')
            ->once()
            ->with("rooms/{$roomId}")
            ->andReturn($expectedResponse);
        
        $result = $this->roomsApi->delete($roomId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetParticipantsCallsCorrectEndpoint(): void
    {
        $roomId = '1234567890abcdef';
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
            ->with("rooms/{$roomId}/participants")
            ->andReturn($expectedResponse);
        
        $result = $this->roomsApi->getParticipants($roomId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testAddParticipantsCallsCorrectEndpoint(): void
    {
        $roomId = '1234567890abcdef';
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
            ->with("rooms/{$roomId}/participants", ['participants' => $participants])
            ->andReturn($expectedResponse);
        
        $result = $this->roomsApi->addParticipants($roomId, $participants);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testUpdateParticipantCallsCorrectEndpoint(): void
    {
        $roomId = '1234567890abcdef';
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
            ->with("rooms/{$roomId}/participants/{$userId}", $updateData)
            ->andReturn($expectedResponse);
        
        $result = $this->roomsApi->updateParticipant($roomId, $userId, $updateData);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testRemoveParticipantCallsCorrectEndpoint(): void
    {
        $roomId = '1234567890abcdef';
        $userId = '123456';
        $expectedResponse = [];
        
        $this->clientMock->shouldReceive('delete')
            ->once()
            ->with("rooms/{$roomId}/participants/{$userId}")
            ->andReturn($expectedResponse);
        
        $result = $this->roomsApi->removeParticipant($roomId, $userId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testStartCallsCorrectEndpoint(): void
    {
        $roomId = '1234567890abcdef';
        $expectedResponse = [
            'roomId' => $roomId,
            'status' => 'started',
            'joinUrl' => 'https://example.com/join/room'
        ];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with("rooms/{$roomId}/start")
            ->andReturn($expectedResponse);
        
        $result = $this->roomsApi->start($roomId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testStopCallsCorrectEndpoint(): void
    {
        $roomId = '1234567890abcdef';
        $expectedResponse = [
            'roomId' => $roomId,
            'status' => 'stopped'
        ];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with("rooms/{$roomId}/stop")
            ->andReturn($expectedResponse);
        
        $result = $this->roomsApi->stop($roomId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGenerateJoinLinkCallsCorrectEndpoint(): void
    {
        $roomId = '1234567890abcdef';
        $options = [
            'userId' => '123456',
            'displayName' => 'Test User',
            'role' => 'presenter'
        ];
        
        $expectedResponse = [
            'joinUrl' => 'https://example.com/join/room/token123456'
        ];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with("rooms/{$roomId}/joinLink", $options)
            ->andReturn($expectedResponse);
        
        $result = $this->roomsApi->generateJoinLink($roomId, $options);
        
        $this->assertEquals($expectedResponse, $result);
    }
} 