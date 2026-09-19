<?php

namespace App\Services\IdentityVerification;

/**
 * 实人认证统一接口
 * 
 * 实现方: AliyunIdentityVerify, TencentIdentityVerify, AlipayIdentityVerify, WechatIdentityVerify
 */
interface IdentityVerifyInterface
{
    /**
     * 身份证二要素核验（姓名+身份证号）
     */
    public function idCardVerify(string $name, string $idNumber, ?string $verifiableType = null, ?int $verifiableId = null): array;

    /**
     * 手机号三要素核验（姓名+手机号+身份证号）
     */
    public function phoneThreeFactorVerify(string $name, string $phone, string $idNumber, ?string $verifiableType = null, ?int $verifiableId = null): array;

    /**
     * 银行卡核验
     */
    public function bankCardVerify(string $name, string $bankCard, ?string $idNumber = null, ?string $phone = null, ?string $verifiableType = null, ?int $verifiableId = null): array;

    /**
     * 服务是否启用
     */
    public function isEnabled(): bool;

    /**
     * 供应商标识
     */
    public function getProviderName(): string;
}
