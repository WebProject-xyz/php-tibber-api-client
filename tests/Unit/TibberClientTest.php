<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Tests\Unit;

use Codeception\Test\Unit;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use WebProject\TibberApiClient\Client\TibberClient;
use WebProject\TibberApiClient\Exception\TibberApiException;
use WebProject\TibberApiClient\Exception\TibberAuthenticationException;
use WebProject\TibberApiClient\Exception\TibberRateLimitException;

class TibberClientTest extends Unit
{
    public function testSuccessfulQuery(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'data' => [
                'viewer' => ['login' => 'test@example.com'],
            ],
        ], JSON_THROW_ON_ERROR), ['http_code' => 200]);

        $httpClient = new MockHttpClient([$mockResponse]);
        $client     = new TibberClient('test-token', $httpClient);

        $result = $client->query('query { viewer { login } }');

        self::assertSame(['viewer' => ['login' => 'test@example.com']], $result);
    }

    public function testAuthenticationFailureHttp401(): void
    {
        $mockResponse = new MockResponse('{"error": "Unauthorized"}', ['http_code' => 401]);
        $httpClient   = new MockHttpClient([$mockResponse]);
        $client       = new TibberClient('invalid-token', $httpClient);

        $this->expectException(TibberAuthenticationException::class);
        $client->query('query { viewer { login } }');
    }

    public function testRateLimitHttp429(): void
    {
        $mockResponse = new MockResponse('{"error": "Too Many Requests"}', ['http_code' => 429]);
        $httpClient   = new MockHttpClient([$mockResponse]);
        $client       = new TibberClient('test-token', $httpClient);

        $this->expectException(TibberRateLimitException::class);
        $client->query('query { viewer { login } }');
    }

    public function testHttpServerError500(): void
    {
        $mockResponse = new MockResponse('Internal server error', ['http_code' => 500]);
        $httpClient   = new MockHttpClient([$mockResponse]);
        $client       = new TibberClient('test-token', $httpClient);

        $this->expectException(TibberApiException::class);
        $client->query('query { viewer { login } }');
    }

    public function testGraphQLErrorInBodyThrowsException(): void
    {
        $mockResponse = new MockResponse(json_encode([
            'errors' => [
                ['message' => 'Cannot query field "invalid" on type "Viewer".'],
            ],
        ], JSON_THROW_ON_ERROR), ['http_code' => 200]);

        $httpClient = new MockHttpClient([$mockResponse]);
        $client     = new TibberClient('test-token', $httpClient);

        $this->expectException(TibberApiException::class);
        $this->expectExceptionMessage('Cannot query field "invalid"');
        $client->query('query { viewer { invalid } }');
    }
}
