<?php

declare(strict_types=1);

namespace CQ\OAuth;

use CQ\OAuth\Flows\FlowProvider;
use CQ\OAuth\Models\TokenModel;
use CQ\OAuth\Models\UserModel;
use CQ\Request\Request;

final class Client
{
    private object $endpoints;

    public function __construct(
        private FlowProvider $flowProvider,
        string $authorizationServer,
        private string $clientId,
        private string $clientSecret,
    ) {
        $this->endpoints = $this->setEndpoints(
            authorizationServer: $authorizationServer
        );

        $flowProvider->setClient(
            client: $this,
            endpoints: $this->endpoints,
            clientId: $this->clientId,
            clientSecret: $this->clientSecret,
        );
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
    public function callback(array $queryParams, string $storedVar): TokenModel
    {
        return $this->flowProvider->callback(
            queryParams: $queryParams,
            storedVar: $storedVar
        );
    }

    /**
     * Refresh access_token, returns access and refresh token
     */
    public function refresh(string $refreshToken): TokenModel
    {
        $authorization = Request::send(
            method: 'POST',
            path: $this->endpoints->token,
            form: [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]
        );

        return new TokenModel(
            accessToken: $authorization->access_token,
            refreshToken: $authorization->refresh_token,
            expiresAt: time() + $authorization->expires_in
        );
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
    public function getUser(string $accessToken): UserModel
    {
        $user = Request::send(
            method: 'GET',
            path: $this->endpoints->userinfo,
            headers: [
                'Authorization' => "Bearer {$accessToken}",
            ]
        );

        $allowed = true;

        if (!$user?->email_verified || !$user?->roles) {
            $allowed = false;
        }

        return new UserModel(
            allowed: $allowed,
            id: $user->sub,
            email: $user->email,
            emailVerified: $user->email_verified,
            roles: $user->roles
        );
    }

    /**
     * Query OAuth server and return endpoints
     */
    private function setEndpoints(string $authorizationServer): object
    {
        $config = Request::send(
            method: 'GET',
            path: $authorizationServer . '/.well-known/openid-configuration'
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
