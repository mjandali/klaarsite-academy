<?php

namespace App\Support;

use InvalidArgumentException;

class VideoEmbedParser
{
    /**
     * @return array{video_url: string|null, video_provider: string|null, video_id: string|null}
     */
    public static function normalize(?string $url): array
    {
        if ($url === null || trim($url) === '') {
            return [
                'video_url' => null,
                'video_provider' => null,
                'video_id' => null,
            ];
        }

        $url = trim($url);
        $parts = parse_url($url);

        if (! is_array($parts) || empty($parts['host'])) {
            throw new InvalidArgumentException('Only YouTube and Vimeo URLs are supported.');
        }

        $host = strtolower($parts['host']);
        $path = trim((string) ($parts['path'] ?? ''), '/');
        $query = [];

        if (! empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            return self::youtubeData(strtok($path, '/'));
        }

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            if ($path === 'watch') {
                return self::youtubeData((string) ($query['v'] ?? ''));
            }

            if (str_starts_with($path, 'embed/')) {
                return self::youtubeData(substr($path, strlen('embed/')));
            }

            if (str_starts_with($path, 'shorts/')) {
                return self::youtubeData(substr($path, strlen('shorts/')));
            }
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com'], true)) {
            return self::vimeoData(strtok($path, '/'));
        }

        if ($host === 'player.vimeo.com' && str_starts_with($path, 'video/')) {
            return self::vimeoData(substr($path, strlen('video/')));
        }

        throw new InvalidArgumentException('Only YouTube and Vimeo URLs are supported.');
    }

    public static function embedUrl(?string $provider, ?string $videoId): ?string
    {
        if (! $provider || ! $videoId) {
            return null;
        }

        return match ($provider) {
            'youtube' => 'https://www.youtube-nocookie.com/embed/'.$videoId,
            'vimeo' => 'https://player.vimeo.com/video/'.$videoId,
            default => null,
        };
    }

    /**
     * @return array{video_url: string, video_provider: string, video_id: string}
     */
    private static function youtubeData(string $videoId): array
    {
        $videoId = trim($videoId);

        if (! preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId)) {
            throw new InvalidArgumentException('Please enter a valid YouTube URL.');
        }

        return [
            'video_url' => 'https://www.youtube.com/watch?v='.$videoId,
            'video_provider' => 'youtube',
            'video_id' => $videoId,
        ];
    }

    /**
     * @return array{video_url: string, video_provider: string, video_id: string}
     */
    private static function vimeoData(string $videoId): array
    {
        $videoId = trim($videoId);

        if (! preg_match('/^[0-9]{6,12}$/', $videoId)) {
            throw new InvalidArgumentException('Please enter a valid Vimeo URL.');
        }

        return [
            'video_url' => 'https://vimeo.com/'.$videoId,
            'video_provider' => 'vimeo',
            'video_id' => $videoId,
        ];
    }
}
