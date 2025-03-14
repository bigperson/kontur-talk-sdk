<?php

namespace Kontur\Talk\Tests\Unit\Exception;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Kontur\Talk\Exception\TalkApiException;
use Kontur\Talk\Exception\TalkClientException;
use Kontur\Talk\Exception\TalkNotFoundException;
use Kontur\Talk\Exception\TalkRateLimitException;
use PHPUnit\Framework\TestCase;

class TalkExceptionsTest extends TestCase
{
    public function testTalkApiExceptionFromClientException(): void
    {
        $responseBody = json_encode(['errorMessage' => 'Bad request']);
        $clientException = new ClientException(
            'Bad request',
            new Request('GET', 'test-endpoint'),
            new Response(400, [], $responseBody)
        );
        
        $exception = new TalkApiException($clientException);
        
        $this->assertEquals('Bad request', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertEquals('Bad request', $exception->getErrorMessage());
        $this->assertSame($clientException, $exception->getPrevious());
    }
    
    public function testTalkApiExceptionFromInvalidJsonResponse(): void
    {
        $responseBody = 'Invalid JSON';
        $clientException = new ClientException(
            'Bad request',
            new Request('GET', 'test-endpoint'),
            new Response(400, [], $responseBody)
        );
        
        $exception = new TalkApiException($clientException);
        
        $this->assertEquals('Bad request', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertEquals('Unknown error', $exception->getErrorMessage());
        $this->assertSame($clientException, $exception->getPrevious());
    }
    
    public function testTalkApiExceptionFromResponseWithoutErrorMessage(): void
    {
        $responseBody = json_encode(['otherField' => 'Some value']);
        $clientException = new ClientException(
            'Bad request',
            new Request('GET', 'test-endpoint'),
            new Response(400, [], $responseBody)
        );
        
        $exception = new TalkApiException($clientException);
        
        $this->assertEquals('Bad request', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertEquals('Unknown error', $exception->getErrorMessage());
        $this->assertSame($clientException, $exception->getPrevious());
    }
    
    public function testTalkClientExceptionFromServerException(): void
    {
        $serverException = new ServerException(
            'Server error',
            new Request('GET', 'test-endpoint'),
            new Response(500)
        );
        
        $exception = new TalkClientException($serverException);
        
        $this->assertEquals('Server error', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
        $this->assertSame($serverException, $exception->getPrevious());
    }
    
    public function testTalkNotFoundExceptionFromClientException(): void
    {
        $clientException = new ClientException(
            'Not found',
            new Request('GET', 'test-endpoint'),
            new Response(404)
        );
        
        $exception = new TalkNotFoundException($clientException);
        
        $this->assertEquals('Not found', $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
        $this->assertSame($clientException, $exception->getPrevious());
    }
    
    public function testTalkRateLimitExceptionFromClientException(): void
    {
        $clientException = new ClientException(
            'Rate limit exceeded',
            new Request('GET', 'test-endpoint'),
            new Response(429)
        );
        
        $exception = new TalkRateLimitException($clientException);
        
        $this->assertEquals('Rate limit exceeded', $exception->getMessage());
        $this->assertEquals(429, $exception->getCode());
        $this->assertSame($clientException, $exception->getPrevious());
    }
    
    public function testTalkApiExceptionWithCustomMessage(): void
    {
        $exception = new TalkApiException('Custom message', 400);
        
        $this->assertEquals('Custom message', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertEquals('Unknown error', $exception->getErrorMessage());
    }
    
    public function testTalkClientExceptionWithCustomMessage(): void
    {
        $exception = new TalkClientException('Custom message', 500);
        
        $this->assertEquals('Custom message', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
    }
    
    public function testTalkNotFoundExceptionWithCustomMessage(): void
    {
        $exception = new TalkNotFoundException('Custom message', 404);
        
        $this->assertEquals('Custom message', $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
    }
    
    public function testTalkRateLimitExceptionWithCustomMessage(): void
    {
        $exception = new TalkRateLimitException('Custom message', 429);
        
        $this->assertEquals('Custom message', $exception->getMessage());
        $this->assertEquals(429, $exception->getCode());
    }
} 