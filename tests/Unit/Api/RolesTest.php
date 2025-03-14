<?php

namespace Kontur\Talk\Tests\Unit\Api;

use Kontur\Talk\Api\Roles;
use Kontur\Talk\TalkClient;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class RolesTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    
    private TalkClient $clientMock;
    private Roles $rolesApi;
    
    protected function setUp(): void
    {
        $this->clientMock = Mockery::mock(TalkClient::class);
        $this->rolesApi = new Roles($this->clientMock);
    }
    
    public function testGetAllCallsCorrectEndpoint(): void
    {
        $expectedResponse = [
            ['roleId' => 'admin', 'title' => 'Administrator'],
            ['roleId' => 'user', 'title' => 'User']
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('roles')
            ->andReturn($expectedResponse);
        
        $result = $this->rolesApi->getAll();
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetWithoutIncludingUsersCount(): void
    {
        $roleId = 'admin';
        $expectedResponse = [
            'roleId' => 'admin', 
            'title' => 'Administrator',
            'permissions' => []
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with("roles/{$roleId}", [])
            ->andReturn($expectedResponse);
        
        $result = $this->rolesApi->get($roleId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetWithIncludingUsersCount(): void
    {
        $roleId = 'admin';
        $expectedResponse = [
            'roleId' => 'admin', 
            'title' => 'Administrator',
            'permissions' => [],
            'usersCount' => 5
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with("roles/{$roleId}", ['includeUsersCount' => 'true'])
            ->andReturn($expectedResponse);
        
        $result = $this->rolesApi->get($roleId, true);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testCreateWithoutDescription(): void
    {
        $title = 'Test Role';
        $permissions = [
            [
                'productId' => 'talk',
                'permissionId' => 'remoteControl'
            ]
        ];
        
        $expectedData = [
            'title' => $title,
            'permissions' => $permissions
        ];
        
        $expectedResponse = [
            'roleId' => 'test-role',
            'title' => $title,
            'permissions' => $permissions
        ];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with('roles', $expectedData)
            ->andReturn($expectedResponse);
        
        $result = $this->rolesApi->create($title, null, $permissions);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testCreateWithDescription(): void
    {
        $title = 'Test Role';
        $description = 'Test role description';
        $permissions = [
            [
                'productId' => 'talk',
                'permissionId' => 'remoteControl'
            ]
        ];
        
        $expectedData = [
            'title' => $title,
            'description' => $description,
            'permissions' => $permissions
        ];
        
        $expectedResponse = [
            'roleId' => 'test-role',
            'title' => $title,
            'description' => $description,
            'permissions' => $permissions
        ];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with('roles', $expectedData)
            ->andReturn($expectedResponse);
        
        $result = $this->rolesApi->create($title, $description, $permissions);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testUpdateWithoutDescription(): void
    {
        $roleId = 'test-role';
        $title = 'Updated Role';
        $permissions = [
            [
                'productId' => 'talk',
                'permissionId' => 'remoteControl'
            ]
        ];
        
        $expectedData = [
            'title' => $title,
            'permissions' => $permissions
        ];
        
        $expectedResponse = [
            'roleId' => $roleId,
            'title' => $title,
            'permissions' => $permissions
        ];
        
        $this->clientMock->shouldReceive('put')
            ->once()
            ->with("roles/{$roleId}", $expectedData)
            ->andReturn($expectedResponse);
        
        $result = $this->rolesApi->update($roleId, $title, null, $permissions);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testUpdateWithDescription(): void
    {
        $roleId = 'test-role';
        $title = 'Updated Role';
        $description = 'Updated role description';
        $permissions = [
            [
                'productId' => 'talk',
                'permissionId' => 'remoteControl'
            ]
        ];
        
        $expectedData = [
            'title' => $title,
            'description' => $description,
            'permissions' => $permissions
        ];
        
        $expectedResponse = [
            'roleId' => $roleId,
            'title' => $title,
            'description' => $description,
            'permissions' => $permissions
        ];
        
        $this->clientMock->shouldReceive('put')
            ->once()
            ->with("roles/{$roleId}", $expectedData)
            ->andReturn($expectedResponse);
        
        $result = $this->rolesApi->update($roleId, $title, $description, $permissions);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetUserRolesCallsCorrectEndpoint(): void
    {
        $userKey = 'test-user-key';
        $expectedResponse = [
            ['roleId' => 'admin', 'title' => 'Administrator'],
            ['roleId' => 'user', 'title' => 'User']
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with("Users/{$userKey}/roles")
            ->andReturn($expectedResponse);
        
        $result = $this->rolesApi->getUserRoles($userKey);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testManageUserRolesCallsCorrectEndpoint(): void
    {
        $userKey = 'test-user-key';
        $addedRoles = ['admin'];
        $removedRoles = ['user'];
        
        $expectedData = [
            'addedRoleIds' => $addedRoles,
            'removedRoleIds' => $removedRoles
        ];
        
        $expectedResponse = [
            ['roleId' => 'admin', 'title' => 'Administrator']
        ];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with("Users/{$userKey}/roles", $expectedData)
            ->andReturn($expectedResponse);
        
        $result = $this->rolesApi->manageUserRoles($userKey, $addedRoles, $removedRoles);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testDeleteCallsCorrectEndpoint(): void
    {
        $roleId = 'test-role';
        $expectedResponse = [];
        
        $this->clientMock->shouldReceive('delete')
            ->once()
            ->with("roles/{$roleId}")
            ->andReturn($expectedResponse);
        
        $result = $this->rolesApi->delete($roleId);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetDefaultCallsCorrectEndpoint(): void
    {
        $expectedResponse = [
            'roleId' => 'default',
            'title' => 'Default Role',
            'permissions' => []
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('roles/default')
            ->andReturn($expectedResponse);
        
        $result = $this->rolesApi->getDefault();
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testUpdateDefaultCallsCorrectEndpoint(): void
    {
        $permissions = [
            [
                'productId' => 'talk',
                'permissionId' => 'remoteControl'
            ]
        ];
        
        $expectedData = [
            'permissions' => $permissions
        ];
        
        $expectedResponse = [];
        
        $this->clientMock->shouldReceive('post')
            ->once()
            ->with('roles/default', $expectedData)
            ->andReturn($expectedResponse);
        
        $result = $this->rolesApi->updateDefault($permissions);
        
        $this->assertEquals($expectedResponse, $result);
    }
    
    public function testGetPermissionsCallsCorrectEndpoint(): void
    {
        $expectedResponse = [
            [
                'productId' => 'talk',
                'productTitle' => 'Talk',
                'permissions' => [
                    [
                        'productId' => 'talk',
                        'permissionId' => 'remoteControl',
                        'title' => 'Remote Control',
                        'constructorVisible' => true
                    ]
                ]
            ]
        ];
        
        $this->clientMock->shouldReceive('get')
            ->once()
            ->with('permissions')
            ->andReturn($expectedResponse);
        
        $result = $this->rolesApi->getPermissions();
        
        $this->assertEquals($expectedResponse, $result);
    }
} 