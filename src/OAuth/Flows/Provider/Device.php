<?php

declare(strict_types=1);

namespace CQ\OAuth\Flows\Provider;

use CQ\OAuth\Exceptions\AuthException;
use CQ\OAuth\Flows\FlowProvider;
use CQ\OAuth\Models\Token;
use CQ\Request\Request;

final class Device extends FlowProvider
{
    public function __construct(
        private string $qrApi = 'https://api.castelnuovo.xyz/qr?data='
    ) {
    }

    public function start(): object
    {
        $path = $this->endpoints->device_authorization . '?client_id=' . $this->clientId;
        $auth_request = Request::send(
            method: 'POST',
            path: $path
        );

        return (object) [
            'uri' => $this->qrApi . urlencode($auth_request->verification_uri_complete),
            'device_code' => $auth_request->device_code,
        ];
    }

    public function callback(array $queryParams, string $storedVar): Token
    {
        try {
            $authorization = Request::send(
                method: 'POST',
                path: $this->endpoints->token,
                form: [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:device_code',
                    'device_code' => $storedVar,
                ]
            );
        } catch (\Throwable $th) {
            var_dump($th->getMessage()); // TODO: check response

            $errorMsg = match ($th->getMessage()) {
                'authorization_pending' => '',
                'expired_token' => 'The request has expired!',
                default => 'Invalid request!',
            };

            throw new AuthException($errorMsg);
        }

        return new Token(
            accessToken: $authorization->access_token,
            refreshToken: $authorization->refresh_token,
            expiresAt: time() + $authorization->expires_in
        );
    }
}
