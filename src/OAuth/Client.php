<?php

declare(strict_types=1);

namespace CQ\OAuth;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\TransferException;
use CQ\OAuth\Exceptions\RequestException;
use CQ\OAuth\Flows\FlowProvider;

final class Client
{
    private object $endpoints;

    public function __construct(
        private FlowProvider $flowProvider,
        private string $authorizationServer,
        private string $clientId,
        private string $clientSecret,
    ) {
        $this->endpoints = $this->setEndpoints();

        $flowProvider->setClient(
            client: $this,
            endpoints: $this->endpoints,
            clientId: $this->clientId,
            clientSecret: $this->clientSecret,
        );
    }

    /**
     * Send request to API
     */
    public function sendRaw(
        string $method,
        string $path,
        array | null $json = null,
        array | null $form = null,
        array | null $headers = null
    ): object {
        $client = new GuzzleClient([
            'base_uri' => $this->authorizationServer,
            'timeout' => 2.0,
        ]);

        $query = null;

        if (strpos($path, '?') !== false) {
            [$path, $query] = explode('?', $path, 2);
        }

        try {
            $response = $client->request($method, $path, [
                'headers' => $headers,
                'query' => $query,
                'json' => $json,
                'form_params' => $form,
            ]);
        } catch (TransferException $error) {
            throw new RequestException(
                message: $error->getMessage(),
                code: $error->getCode(),
                previous: $error
            );
        }

        $output = $response->getBody()->getContents();

        // Handle NoContent responses
        if (! $output) {
            return (object) [];
        }

        return json_decode($output);
    }

    /**
     * Start OAuth flow, returns data based on flow type
     */
    public function start(): object
    {
        return $this->flowProvider->start();
    }

    /**
     * Callback OAuth flow, perform callback operation
     */
    public function callback(array $queryParams, string $storedVar): object
    {
        return $this->flowProvider->callback(
            queryParams: $queryParams,
            storedVar: $storedVar
        );
    }

    /**
     * Refresh access_token, returns access and refresh token
     */
    public function refresh(string $refreshToken)
    {
        $authorization = $this->sendRaw(
            method: 'POST',
            path: $this->endpoints->token,
            form: [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]
        );

        return (object) [
            'access_token' => $authorization->access_token,
            'refresh_token' => $authorization->refresh_token,
            'expires_at' => time() + $authorization->expires_in,
        ];
    }

    /**
     * Get logout url
     */
    public function logout(): string
    {
        return $this->endpoints->logout . '?client_id=' . $this->clientId;
    }

    /**
     * Get user info and check if user is allowed to login
     */
    public function getUser(string $accessToken): object
    {
        $user = $this->sendRaw(
            method: 'GET',
            path: $this->endpoints->userinfo,
            headers: [
                'Authorization' => "Bearer {$accessToken}",
            ]
        );

        $allowed = true;

        if (! $user?->email_verified || ! $user?->roles) {
            $allowed = false;
        }

        return (object) [
            'allowed' => $allowed,
            'id' => $user->sub,
            'email' => $user->email,
            'email_verified' => $user->email_verified,
            'roles' => $user?->roles,
        ];
    }

    /**
     * Query OAuth server and return endpoints
     */
    private function setEndpoints(): object
    {
        $config = $this->sendRaw(
            method: 'GET',
            path: '/.well-known/openid-configuration'
        );

        return (object) [
            'authorization' => $config->authorization_endpoint,
            'device_authorization' => $config->device_authorization_endpoint,
            'token' => $config->token_endpoint,
            'userinfo' => $config->userinfo_endpoint,
            'logout' => $config->end_session_endpoint,
        ];
    }
}
