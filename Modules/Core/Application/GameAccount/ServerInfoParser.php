<?php

declare(strict_types=1);

namespace Modules\Core\Application\GameAccount;

final class ServerInfoParser
{
    public function parse(string $output): ServerInfo
    {
        return new ServerInfo(
            onlinePlayers: $this->integerFrom($output, [
                '/\bConnected players:\s*(\d+)/i',
                '/\bPlayers online:\s*(\d+)/i',
                '/\bPlayer count:\s*(\d+)/i',
            ]) ?? 0,
            connectionPeak: $this->integerFrom($output, [
                '/\bConnection peak:\s*(\d+)/i',
                '/\bMaximum connected players:\s*(\d+)/i',
                '/\(\s*Max:\s*(\d+)\s*\)/i',
            ]),
            uptime: $this->uptimeFrom($output),
            version: $this->versionFrom($output),
        );
    }

    /**
     * @param  array<int, string>  $patterns
     */
    private function integerFrom(string $output, array $patterns): ?int
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $output, $matches) === 1) {
                return (int) $matches[1];
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $patterns
     */
    private function stringFrom(string $output, array $patterns): ?string
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $output, $matches) === 1) {
                $value = trim($matches[1]);

                return $value !== '' ? rtrim($value, '.') : null;
            }
        }

        return null;
    }

    private function uptimeFrom(string $output): ?string
    {
        $uptime = $this->stringFrom($output, [
            '/\bServer uptime:\s*([^\r\n]+)/i',
            '/\bUptime:\s*([^\r\n]+)/i',
        ]);

        if ($uptime === null) {
            return null;
        }

        preg_match_all(
            '/(\d+)\s*(Day|Hour|Minute|Second)(?:\(s\)|s)?/i',
            $uptime,
            $matches,
            PREG_SET_ORDER,
        );

        if ($matches === []) {
            return $uptime;
        }

        $abbreviations = [
            'day' => 'd',
            'hour' => 'h',
            'minute' => 'm',
            'second' => 's',
        ];
        $parts = [];

        foreach ($matches as $match) {
            $value = (int) $match[1];

            if ($value > 0) {
                $parts[] = $value.$abbreviations[strtolower($match[2])];
            }
        }

        if ($parts === []) {
            return '0s';
        }

        return implode(' ', array_slice($parts, 0, 2));
    }

    private function versionFrom(string $output): ?string
    {
        if (preg_match(
            '/\b(TrinityCore|AzerothCore|CMaNGOS|MaNGOS|vMaNGOS|SkyFire)(?:\s+rev\.?\s+([^\s(]+))?/i',
            $output,
            $matches,
        ) !== 1) {
            return null;
        }

        $core = $matches[1];
        $revision = isset($matches[2]) ? trim($matches[2]) : '';

        return $revision === '' ? $core : "{$core} {$revision}";
    }
}
