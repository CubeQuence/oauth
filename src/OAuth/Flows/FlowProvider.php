<?php

declare(strict_types=1);

namespace CQ\OAuth\Flows;

use CQ\OAuth\Client;
use CQ\OAuth\Models\TokenModel;

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
    abstract public function callback(array $queryParams, string $storedVar): TokenModel;
}
