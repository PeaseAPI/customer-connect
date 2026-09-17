<?php

namespace App\Services\Approval;

interface ApprovalServiceInterface
{
    public function createInstance(array $data): string;
    public function getInstance(string $instanceId): array;
    public function registerCallback(string $url): void;
    public function testConnection(): bool;
    public function getConfig(): array;
    public function setConfig(array $config): void;
}
