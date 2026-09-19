<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS service configuration
    |--------------------------------------------------------------------------
    */
    'sms' => [
        'driver' => env('SMS_DRIVER', 'aliyun'),
    ],

    'aliyun_sms' => [
        'access_key_id' => env('ALIYUN_SMS_ACCESS_KEY_ID'),
        'access_key_secret' => env('ALIYUN_SMS_ACCESS_KEY_SECRET'),
        'sign_name' => env('ALIYUN_SMS_SIGN_NAME'),
        'verify_template' => env('ALIYUN_SMS_VERIFY_TEMPLATE'),
        'region' => env('ALIYUN_SMS_REGION', 'cn-hangzhou'),
    ],

    'tencent_sms' => [
        'secret_id' => env('TENCENT_SMS_SECRET_ID'),
        'secret_key' => env('TENCENT_SMS_SECRET_KEY'),
        'app_id' => env('TENCENT_SMS_APP_ID'),
        'sign_name' => env('TENCENT_SMS_SIGN_NAME'),
        'verify_template' => env('TENCENT_SMS_VERIFY_TEMPLATE'),
        'region' => env('TENCENT_SMS_REGION', 'ap-guangzhou'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment service configuration
    |--------------------------------------------------------------------------
    */
    'alipay' => [
        'app_id' => env('ALIPAY_APP_ID'),
        'private_key' => env('ALIPAY_PRIVATE_KEY'),
        'public_key' => env('ALIPAY_PUBLIC_KEY'),
        'sandbox' => env('ALIPAY_SANDBOX', false),
        'notify_url' => env('ALIPAY_NOTIFY_URL'),
        'return_url' => env('ALIPAY_RETURN_URL'),
    ],

    'wechat_pay' => [
        'app_id' => env('WECHAT_PAY_APP_ID'),
        'mch_id' => env('WECHAT_PAY_MCH_ID'),
        'api_key' => env('WECHAT_PAY_API_KEY'),
        'cert_path' => env('WECHAT_PAY_CERT_PATH'),
        'key_path' => env('WECHAT_PAY_KEY_PATH'),
        'notify_url' => env('WECHAT_PAY_NOTIFY_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Social login configuration
    |--------------------------------------------------------------------------
    */
    'wechat_open' => [
        'app_id' => env('WECHAT_OPEN_APP_ID'),
        'app_secret' => env('WECHAT_OPEN_APP_SECRET'),
        'redirect_url' => env('WECHAT_OPEN_REDIRECT_URL'),
    ],

    'dingtalk' => [
        'app_key' => env('DINGTALK_APP_KEY'),
        'app_secret' => env('DINGTALK_APP_SECRET'),
        'corp_id' => env('DINGTALK_CORP_ID'),
    ],

    'feishu' => [
        'app_id' => env('FEISHU_APP_ID'),
        'app_secret' => env('FEISHU_APP_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Approval service configuration
    |--------------------------------------------------------------------------
    */
        'approval' => [
        'driver' => env('APPROVAL_DRIVER', 'internal'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI service configuration
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
    ],

    /*
    |--------------------------------------------------------------------------
    | China Mobile Cloud (移动云) Integration
    |--------------------------------------------------------------------------
    |
    | 手机号实名认证 + 内容审核
    | 文档: https://ecloud.10086.cn/op-help-center/doc/outline/32923
    |       https://ecloud.10086.cn/op-help-center/doc/outline/41497
    |
    */
    'ecloud' => [
        'access_key' => env('ECLOUD_ACCESS_KEY', ''),
        'secret_key' => env('ECLOUD_SECRET_KEY', ''),
        'gateway_url' => env('ECLOUD_GATEWAY_URL', 'https://gateway.ecloud.10086.cn/api'),
        'timeout' => env('ECLOUD_TIMEOUT', 30),
        'retry_times' => env('ECLOUD_RETRY_TIMES', 2),

        // 手机号实名认证
        'phone_verify_enabled' => env('ECLOUD_PHONE_VERIFY_ENABLED', false),

        // 内容审核
        'content_audit_enabled' => env('ECLOUD_CONTENT_AUDIT_ENABLED', false),
        'audit_categories' => ['politics', 'violence', 'porn', 'contraband', 'ad', 'abuse'],
        'audit_block_action' => env('ECLOUD_AUDIT_BLOCK_ACTION', 'block'), // block|flag
        'audit_fail_open' => env('ECLOUD_AUDIT_FAIL_OPEN', true), // 审核异常时是否放行
        'audit_callback_url' => env('ECLOUD_AUDIT_CALLBACK_URL', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Identity Verification (实人认证) - 多Provider驱动
    |--------------------------------------------------------------------------
    |
    | driver: aliyun | tencent | alipay | wechat
    | 文档: 见 客户通/11-多平台认证与审核集成文档.md
    |
    */
    'identity_verify' => [
        'driver' => env('IDENTITY_VERIFY_DRIVER', 'aliyun'),

        // 阿里云实人认证 (AK/SK RPC签名)
        'aliyun' => [
            'enabled' => env('ALIYUN_IDENTITY_ENABLED', false),
            'access_key_id' => env('ALIYUN_IDENTITY_ACCESS_KEY_ID', ''),
            'access_key_secret' => env('ALIYUN_IDENTITY_ACCESS_KEY_SECRET', ''),
            'endpoint' => env('ALIYUN_IDENTITY_ENDPOINT', 'idvi.cn-shanghai.aliyuncs.com'),
        ],

        // 腾讯云人脸核身 (TC3-HMAC-SHA256)
        'tencent' => [
            'enabled' => env('TENCENT_IDENTITY_ENABLED', false),
            'secret_id' => env('TENCENT_IDENTITY_SECRET_ID', ''),
            'secret_key' => env('TENCENT_IDENTITY_SECRET_KEY', ''),
            'region' => env('TENCENT_IDENTITY_REGION', 'ap-guangzhou'),
        ],

        // 支付宝实名认证 (RSA2)
        'alipay' => [
            'enabled' => env('ALIPAY_IDENTITY_ENABLED', false),
            'app_id' => env('ALIPAY_IDENTITY_APP_ID', ''),
            'private_key' => env('ALIPAY_IDENTITY_PRIVATE_KEY', ''),
            'public_key' => env('ALIPAY_IDENTITY_PUBLIC_KEY', ''),
            'gateway' => env('ALIPAY_IDENTITY_GATEWAY', 'https://openapi.alipay.com/gateway.do'),
        ],

        // 微信实名认证 (商户MD5签名, 灰度中)
        'wechat' => [
            'enabled' => env('WECHAT_IDENTITY_ENABLED', false),
            'app_id' => env('WECHAT_IDENTITY_APP_ID', ''),
            'mch_id' => env('WECHAT_IDENTITY_MCH_ID', ''),
            'api_key' => env('WECHAT_IDENTITY_API_KEY', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Phone Verification (号码认证) - 多Provider驱动
    |--------------------------------------------------------------------------
    |
    | driver: ecloud | aliyun | tencent
    |
    */
    'phone_verify' => [
        'driver' => env('PHONE_VERIFY_DRIVER', 'ecloud'),

        // 移动云 (复用 services.ecloud 凭证)
        'ecloud' => [
            'enabled' => env('ECLOUD_PHONE_VERIFY_ENABLED', false),
        ],

        // 阿里云号码认证 PNVS (AK/SK RPC签名)
        'aliyun' => [
            'enabled' => env('ALIYUN_PHONE_VERIFY_ENABLED', false),
            'access_key_id' => env('ALIYUN_PHONE_VERIFY_ACCESS_KEY_ID', ''),
            'access_key_secret' => env('ALIYUN_PHONE_VERIFY_ACCESS_KEY_SECRET', ''),
            'endpoint' => env('ALIYUN_PHONE_VERIFY_ENDPOINT', 'dypnsapi.cn-hangzhou.aliyuncs.com'),
        ],

        // 腾讯云 (手机号三要素, TC3签名)
        'tencent' => [
            'enabled' => env('TENCENT_PHONE_VERIFY_ENABLED', false),
            'secret_id' => env('TENCENT_PHONE_VERIFY_SECRET_ID', ''),
            'secret_key' => env('TENCENT_PHONE_VERIFY_SECRET_KEY', ''),
            'region' => env('TENCENT_PHONE_VERIFY_REGION', 'ap-guangzhou'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security (内容审核) - 多Provider驱动
    |--------------------------------------------------------------------------
    |
    | driver: ecloud | aliyun | tencent
    |
    */
    'content_security' => [
        'driver' => env('CONTENT_SECURITY_DRIVER', 'ecloud'),

        // 移动云 (复用 services.ecloud 凭证)
        'ecloud' => [
            'enabled' => env('ECLOUD_CONTENT_AUDIT_ENABLED', false),
        ],

        // 阿里云内容安全 (ACS3签名)
        'aliyun' => [
            'enabled' => env('ALIYUN_CONTENT_SECURITY_ENABLED', false),
            'access_key_id' => env('ALIYUN_CONTENT_SECURITY_ACCESS_KEY_ID', ''),
            'access_key_secret' => env('ALIYUN_CONTENT_SECURITY_ACCESS_KEY_SECRET', ''),
            'endpoint' => env('ALIYUN_CONTENT_SECURITY_ENDPOINT', 'green.cn-shanghai.aliyuncs.com'),
        ],

        // 腾讯云内容安全 CMS (TC3签名)
        'tencent' => [
            'enabled' => env('TENCENT_CONTENT_SECURITY_ENABLED', false),
            'secret_id' => env('TENCENT_CONTENT_SECURITY_SECRET_ID', ''),
            'secret_key' => env('TENCENT_CONTENT_SECURITY_SECRET_KEY', ''),
            'region' => env('TENCENT_CONTENT_SECURITY_REGION', 'ap-guangzhou'),
        ],

        // 异步审核回调地址(阿里云/腾讯云视频音频)
        'callback_url' => env('CONTENT_SECURITY_CALLBACK_URL', ''),
    ],

];
