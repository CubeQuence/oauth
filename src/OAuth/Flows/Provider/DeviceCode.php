<?php

declare(strict_types=1);

namespace CQ\OAuth\Flows\Provider;

use CQ\OAuth\Exceptions\OAuthException;
use CQ\OAuth\Flows\FlowProvider;
use CQ\OAuth\Models\TokenModel;
use CQ\Request\Request;
use CQ\Request\Exceptions\BadResponseException;

final class DeviceCode extends FlowProvider
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
            path: $path,
            form: [
                "scope" => "offline_access",
            ]
        );

        return (object) [
            'uri' => $this->qrApi . urlencode($auth_request->verification_uri_complete),
            'device_code' => $auth_request->device_code,
        ];
    }

    public function callback(array $queryParams, string $storedVar): TokenModel
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
        } catch (BadResponseException $error) {
            $errorCode = json_decode($error->getMessage())->error;

            $errorMsg = match ($errorCode) {
                'authorization_pending' => '',
                'expired_token' => 'The request has expired!',
                default => 'Invalid request!',
            };

            throw new OAuthException($errorMsg);
        }

        return new TokenModel(
            accessToken: $authorization->access_token,
            refreshToken: $authorization->refresh_token,
            expiresAt: time() + $authorization->expires_in
        );
    }
}
