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
    | 短信服务配置
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
    | 支付服务配置
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
    | 社交登录配置
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
    | 审批服务配置
    |--------------------------------------------------------------------------
    */
    'approval' => [
        'driver' => env('APPROVAL_DRIVER', 'internal'),
    ],

];
