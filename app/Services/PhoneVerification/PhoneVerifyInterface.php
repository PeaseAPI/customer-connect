<?php

namespace App\Services\PhoneVerification;

/**
 * 号码认证统一接口
 * 
 * 实现方: EcloudPhoneVerify, AliyunPhoneVerify, TencentPhoneVerify
 */
interface PhoneVerifyInterface
{
    /**
     * 二要素认证（姓名+手机号）
     */
    public function twoFactorVerify(string $name, string $phone, ?string $verifiableType = null, ?int $verifiableId = null): array;

    /**
     * 三要素认证（姓名+手机号+身份证号）
     */
    public function threeFactorVerify(string $name, string $phone, string $idNumber, ?string $verifiableType = null, ?int $verifiableId = null): array;

    /**
     * 服务是否启用
     */
    public function isEnabled(): bool;

    /**
     * 供应商标识
     */
    public function getProviderName(): string;
}
