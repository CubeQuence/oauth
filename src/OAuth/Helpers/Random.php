<?php

declare(strict_types=1);

namespace CQ\OAuth\Helpers;

final class Random
{
    public static function get(int $length = 32): string
    {
        $randomString = '';

        while (($len = strlen(string: $randomString)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes(length: $size);

            $randomString .= substr(
                str_replace(
                    search: ['/', '+', '=', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                    replace: '',
                    subject: base64_encode($bytes)
                ),
                0,
                $size
            );
        }

        return $randomString;
    }
}
