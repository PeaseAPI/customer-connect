<?php

namespace App\Services\SocialLogin;

interface SocialLoginInterface
{
    public function getRedirectUrl(): string;
    public function handleCallback(string $code): array;
    public function testConnection(): bool;
    public function getConfig(): array;
    public function setConfig(array $config): void;
}
