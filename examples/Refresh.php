<?php

declare(strict_types=1);

use CQ\OAuth\Client;
use CQ\OAuth\Flows\Provider\AuthorizationCode;

try {
    $client = new Client(
        flowProvider: new AuthorizationCode(redirectUri: 'http://127.0.0.1'),
        authorizationServer: 'http://127.0.0.1:8200',
        clientId: 'f614dc98-353d-4e7a-9503-bcfe319f09e5',
        clientSecret: 'GyICvwFbp2Ihf2snmcxA4gZmEhbbelqAL0oGzEd19Xg'
    );

    $refresh_token = '32ckshkhaskhkaldsjaldjas';

    $newTokens = $client->refresh(
        refreshToken: $refresh_token
    );

    echo json_encode([
        'accessToken' => $newTokens->getAccessToken(),
        'refreshToken' => $newTokens->getRefreshToken(),
        'expiresAt' => $newTokens->getExpiresAt(),
    ]);
} catch (\Throwable $th) {
    echo $th->getMessage();
    exit;
}
