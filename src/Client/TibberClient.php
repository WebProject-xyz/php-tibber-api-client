<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Client;

use SensitiveParameter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;
use WebProject\TibberApiClient\Exception\TibberApiException;
use WebProject\TibberApiClient\Exception\TibberAuthenticationException;
use WebProject\TibberApiClient\Exception\TibberRateLimitException;

use function is_array;
use function sprintf;

class TibberClient implements TibberClientInterface
{
    public const DEFAULT_ENDPOINT   = 'https://api.tibber.com/v1-beta/gql';
    public const DEFAULT_USER_AGENT = 'webproject-xyz/php-tibber-api-client';

    private readonly HttpClientInterface $httpClient;

    public function __construct(
        #[SensitiveParameter]
        private readonly string $apiToken,
        ?HttpClientInterface $httpClient = null,
        private readonly string $endpoint = self::DEFAULT_ENDPOINT,
        private readonly string $userAgent = self::DEFAULT_USER_AGENT,
    ) {
        $this->httpClient = $httpClient ?? HttpClient::create();
    }

    /**
     * @param array<string, mixed> $variables
     *
     * @return array<string, mixed>
     */
    public function query(string $query, array $variables = []): array
    {
        $payload = ['query' => $query];
        if ([] !== $variables) {
            $payload['variables'] = $variables;
        }

        try {
            $response = $this->httpClient->request('POST', $this->endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                    'User-Agent'    => $this->userAgent,
                ],
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();
            $content    = $response->getContent(false);
        } catch (Throwable $e) {
            throw new TibberApiException('HTTP request to Tibber API failed: ' . $e->getMessage(), 0, $e);
        }

        if (401 === $statusCode || 403 === $statusCode) {
            throw new TibberAuthenticationException(sprintf('Tibber API authentication failed with HTTP %d. Verify your API token.', $statusCode), $statusCode);
        }

        if (429 === $statusCode) {
            throw new TibberRateLimitException('Tibber API rate limit exceeded (HTTP 429). Reduce request frequency.', $statusCode);
        }

        if ($statusCode >= 400) {
            throw new TibberApiException(sprintf('Tibber API returned HTTP %d: %s', $statusCode, $content), $statusCode);
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($content, true);

        if (!is_array($decoded)) {
            throw new TibberApiException('Failed to decode JSON response from Tibber API: ' . $content);
        }

        if (isset($decoded['errors']) && is_array($decoded['errors']) && [] !== $decoded['errors']) {
            /** @var array<int, array<string, mixed>> $errors */
            $errors       = $decoded['errors'];
            $firstMessage = (string) ($errors[0]['message'] ?? 'Unknown GraphQL error');

            $lower = strtolower($firstMessage);
            if (str_contains($lower, 'unauthorized') || str_contains($lower, 'forbidden') || str_contains($lower, 'invalid token')) {
                throw new TibberAuthenticationException('GraphQL authentication error: ' . $firstMessage, 401, errors: $decoded);
            }

            if (str_contains($lower, 'rate limit') || str_contains($lower, 'too many requests')) {
                throw new TibberRateLimitException('GraphQL rate limit error: ' . $firstMessage, 429, errors: $decoded);
            }

            throw new TibberApiException('GraphQL error: ' . $firstMessage, 0, null, $decoded);
        }

        /** @var array<string, mixed> $data */
        $data = $decoded['data'] ?? [];

        return $data;
    }
}
