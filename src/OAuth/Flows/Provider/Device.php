<?php

declare(strict_types=1);

namespace CQ\OAuth\Flows\Provider;

use CQ\OAuth\Exceptions\AuthException;
use CQ\OAuth\Flows\FlowProvider;

final class Device extends FlowProvider
{
    public function __construct(
        private string $qrApi = 'https://api.castelnuovo.xyz/qr?data='
    ) {
    }

    public function start(): object
    {
        $path = $this->endpoints->device_authorization . '?client_id=' . $this->clientId;
        $auth_request = $this->client->sendRaw(
            method: 'POST',
            path: $path
        );

        return (object) [
            'uri' => $this->qrApi . urlencode($auth_request->verification_uri_complete),
            'device_code' => $auth_request->device_code,
        ];
    }

    public function callback(array $queryParams, string $storedVar): object
    {
        try {
            $authorization = $this->client->sendRaw(
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
            $error = json_decode(json: $th->getMessage())->error;

            match ($error) {
                'authorization_pending' => throw new AuthException(),
                'expired_token' => throw new AuthException('The request has expired!'),
                default => throw new AuthException('Invalid request!'),
            };
        }

        return (object) [
            'access_token' => $authorization->access_token,
            'refresh_token' => $authorization->refresh_token,
            'expires_at' => time() + $authorization->expires_in,
        ];
    }
}
