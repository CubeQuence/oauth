<?php

declare(strict_types=1);

use CQ\OAuth\Client;
use CQ\OAuth\Flows\Provider\AuthorizationCode;

session_start();

try {
    $client = new Client(
        flowProvider: new AuthorizationCode(redirectUri: 'http://127.0.0.1'),
        authorizationServer: 'http://127.0.0.1:8200',
        clientId: 'f614dc98-353d-4e7a-9503-bcfe319f09e5',
        clientSecret: 'GyICvwFbp2Ihf2snmcxA4gZmEhbbelqAL0oGzEd19Xg'
    );

    if (! isset($_GET['code'])) {
        $start = $client->start();

        // Save state
        $_SESSION['state'] = $start->state;

        // Redirect user to login uri
        header('Location: ' . $start->uri);

        // Stop program
        exit;
    }

    $tokens = $client->callback(
        queryParams: $_GET,
        storedVar: $_SESSION['state']
    );

    $user = $client->getUser(
        accessToken: $tokens->getAccessToken()
    );

    // Log user in
    echo json_encode([
        'tokens' => [
            'accessToken' => $tokens->getAccessToken(),
            'refreshToken' => $tokens->getRefreshToken(),
            'expiresAt' => $tokens->getExpiresAt(),
        ],
        'user' => [
            'allowed' => $user->isAllowed(),
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'emailVerified' => $user->isEmailVerified(),
            'roles' => $user->getRoles(),
        ],
    ]);
} catch (\Throwable $th) {
    echo $th->getMessage();
    exit;
}
