<?php

namespace App\Support;

final class HostRelativeUrl
{
    public static function normalize(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return $url;
        }

        $parts = parse_url(trim($url));
        $configuredHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (! is_array($parts) || ! is_string($parts['host'] ?? null) || ! is_string($configuredHost)) {
            return $url;
        }

        if (strcasecmp($parts['host'], $configuredHost) !== 0) {
            return $url;
        }

        $path = $parts['path'] ?? '/';
        if ($path === '') {
            $path = '/';
        }

        return $path
            .(isset($parts['query']) ? '?'.$parts['query'] : '')
            .(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
    }
}
