<?php

declare(strict_types=1);

namespace WebProject\TibberApiClient\Client;

use WebProject\TibberApiClient\Exception\TibberApiException;

interface TibberClientInterface
{
    /**
     * Executes a GraphQL query or mutation against the Tibber API.
     *
     * @param string               $query     GraphQL query or mutation string
     * @param array<string, mixed> $variables Variables for the GraphQL operation
     *
     * @return array<string, mixed> The 'data' array from the GraphQL response
     *
     * @throws TibberApiException If an HTTP error or GraphQL error occurs
     */
    public function query(string $query, array $variables = []): array;
}
