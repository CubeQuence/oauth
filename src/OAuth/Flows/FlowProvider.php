<?php

declare(strict_types=1);

namespace OAuth\Flows;

use OAuth\Client;

abstract class FlowProvider
{
    protected Client $client;
    protected object $endpoints;
    protected string $clientId;
    protected string $clientSecret;

    /**
     * Set client instance for flow types that
     * need to get client config
     */
    public function setClient(
        Client $client,
        object $endpoints,
        string $clientId,
        string $clientSecret,
    ): void {
        $this->client = $client;
        $this->endpoints = $endpoints;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * Start OAuth flow
     */
    abstract public function start(): object;

    /**
     * Callback OAuth flow
     */
    abstract public function callback(array $queryParams, string $storedVar): object;
}
