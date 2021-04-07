<?php

declare(strict_types=1);

namespace CQ\OAuth\Flows\Provider;

use CQ\OAuth\Exceptions\AuthException;
use CQ\OAuth\Flows\FlowProvider;
use CQ\OAuth\Helpers\Random;
use CQ\OAuth\Models\Token;
use CQ\Request\Request;

final class AuthorizationCode extends FlowProvider
{
    public function __construct(
        private string $redirectUri
    ) {
    }

    /**
     * Return uri and state for Authorization Code flow
     */
    public function start(): object
    {
        $state = Random::get(length: 32);

        $authUri = "{$this->endpoints->authorization}";
        $authUri .= "?client_id={$this->clientId}";
        $authUri .= "&state={$state}";
        $authUri .= '&redirect_uri=' . urlencode($this->redirectUri);
        $authUri .= '&response_mode=query&response_type=code&approval_prompt=auto';
        $authUri .= '&scope=offline_access';

        return (object) [
            'uri' => $authUri,
            'state' => $state,
        ];
    }

    public function callback(array $queryParams, string $storedVar): Token
    {
        $code = $queryParams['code'];
        $state = $queryParams['state'];

        if ($storedVar !== $state) {
            throw new AuthException('Invalid state!');
        }

        $authorization = Request::send(
            method: 'POST',
            path: $this->endpoints->token,
            form: [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->redirectUri,
                'code' => $code,
            ]
        );

        return new Token(
            accessToken: $authorization->access_token,
            refreshToken: $authorization->refresh_token,
            expiresAt: time() + $authorization->expires_in
        );
    }
}
