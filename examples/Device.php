<?php

declare(strict_types=1);

use CQ\OAuth\Client;
use CQ\OAuth\Flows\Provider\Device;

session_start();

try {
    $client = new Client(
        flowProvider: new Device(
            // This API will be used if variable is not set
            qrApi: 'https://api.castelnuovo.xyz/qr?data='
        ),
        authorizationServer: 'http://127.0.0.1:8200',
        clientId: 'f614dc98-353d-4e7a-9503-bcfe319f09e5',
        clientSecret: 'GyICvwFbp2Ihf2snmcxA4gZmEhbbelqAL0oGzEd19Xg'
    );

    if (! isset($_GET['confirm'])) {
        $start = $client->start();

        // Save device_code
        $_SESSION['device_code'] = $start->device_code;

        // Redirect user to login uri
        header('Location: ' . $start->uri);

        // Stop program
        exit;
    }

    /**
     * User gets redirected to an QR code,
     * this can be embedded on your login page.
     *
     * This page should send AJAX request to Device.php?confirm,
     * to check if the user has confirmed the QR code
     * on their mobile device.
     */

    $tokens = $client->callback(
        queryParams: $_GET,
        storedVar: $_SESSION['device_code']
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
