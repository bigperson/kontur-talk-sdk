<?php

namespace Kontur\Talk\Tests\Unit\Api;

use Kontur\Talk\Api\Users;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\TalkClient;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class UsersTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    
    private TalkClient $clientMock;
    private Users $usersApi;
    
    protected function setUp(): void
    {
        $this->clientMock = Mockery::mock(TalkClient::class);
        $this->usersApi = new Users($this->clientMock);
    }
    
    public function testGetByKeyCallsCorrectEndpoint(): void
    {
        $userKey = 'test-user-key';
        $expectedResponse = ['login' => 'testuser', 'email' => 'test@example.com'];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with("users/{$userKey}")
            ->andReturn($expectedResponse);
        
        $result = $this->usersApi->getByKey($userKey);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testScanWithDefaultParameters(): void
    {
        $expectedResponse = ['users' => [], 'offset' => 'next-page-key'];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('users/scan', [
                'top' => 100,
                'includeDisabled' => 'false'
            ])
            ->andReturn($expectedResponse);
        
        $result = $this->usersApi->scan();
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testScanWithCustomParameters(): void
    {
        $expectedResponse = ['users' => [], 'offset' => 'next-page-key'];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('users/scan', [
                'top' => 50,
                'includeDisabled' => 'true',
                'offset' => 'page-key',
                'role' => 'admin'
            ])
            ->andReturn($expectedResponse);
        
        $result = $this->usersApi->scan(50, 'page-key', 'admin', true);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testScanTopIsLimitedTo1000(): void
    {
        $expectedResponse = ['users' => [], 'offset' => 'next-page-key'];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('users/scan', [
                'top' => 1000, // Should be limited to 1000 even if higher value is provided
                'includeDisabled' => 'false'
            ])
            ->andReturn($expectedResponse);
        
        $result = $this->usersApi->scan(2000);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetWithDefaultParameters(): void
    {
        $expectedResponse = ['users' => []];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('users', [
                'top' => 100,
                'skip' => 0,
                'includeDisabled' => 'false',
                'fillInMeetingStatus' => 'false'
            ])
            ->andReturn($expectedResponse);
        
        $result = $this->usersApi->get();
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetWithCustomParameters(): void
    {
        $expectedResponse = ['users' => []];
        $emails = ['user1@example.com', 'user2@example.com'];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('users', [
                'top' => 50,
                'skip' => 10,
                'includeDisabled' => 'true',
                'fillInMeetingStatus' => 'true',
                'query' => 'search term',
                'role' => 'admin',
                'email' => $emails
            ])
            ->andReturn($expectedResponse);
        
        $result = $this->usersApi->get(50, 10, 'search term', $emails, 'admin', true, true);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testCreateOrUpdateThrowsExceptionWhenTooManyUsers(): void
    {
        $this->expectException(TalkClientException::class);
        
        $users = array_fill(0, 31, ['email' => 'test@example.com']);
        
        $this->usersApi->createOrUpdate($users);
    }
    
    public function testCreateOrUpdateCallsCorrectEndpoint(): void
    {
        $users = [
            [
                'email' => 'user1@example.com',
                'firstname' => 'User',
                'surname' => 'One'
            ]
        ];
        
        $expectedResponse = ['users' => $users];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with('users', $users)
            ->andReturn($expectedResponse);
        
        $result = $this->usersApi->createOrUpdate($users);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testSetPermissionsCallsCorrectEndpoint(): void
    {
        $userKey = 'test-user-key';
        $disabled = true;
        $expectedResponse = [];
        
        $this->clientMock->shouldReceive('put')
            ->once()
            ->with("users/{$userKey}/permissions", ['disabled' => $disabled])
            ->andReturn($expectedResponse);
        
        $result = $this->usersApi->setPermissions($userKey, $disabled);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testDeleteCallsCorrectEndpoint(): void
    {
        $userKey = 'test-user-key';
        $expectedResponse = [];
        
        $this->clientMock->shouldReceive('delete')
            ->once()
            ->with("users/{$userKey}")
            ->andReturn($expectedResponse);
        
        $result = $this->usersApi->delete($userKey);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testSyncAvatarCallsCorrectEndpoint(): void
    {
        $userKey = 'test-user-key';
        $expectedResponse = [];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with("users/{$userKey}/avatar/sync")
            ->andReturn($expectedResponse);
        
        $result = $this->usersApi->syncAvatar($userKey);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testDeleteAvatarCallsCorrectEndpoint(): void
    {
        $userKey = 'test-user-key';
        $expectedResponse = [];
        
        $this->clientMock->shouldReceive('delete')
            ->once()
            ->with("users/{$userKey}/avatar")
            ->andReturn($expectedResponse);
        
        $result = $this->usersApi->deleteAvatar($userKey);
        
        $this->assertEquals($expectedResponse, $result);
    }
} 