<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 多平台Provider支持:
 * - identity_verifications / content_audit_logs 添加 provider 列(记录使用哪个供应商)
 * - content_audit_logs 添加 content_url 列(图片/视频/音频审核的资源地址)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identity_verifications', function (Blueprint $table) {
            $table->string('provider', 20)->default('ecloud')->after('company_id')->index(); // ecloud/aliyun/tencent/alipay/wechat
        });

        Schema::table('content_audit_logs', function (Blueprint $table) {
            $table->string('provider', 20)->default('ecloud')->after('company_id')->index(); // ecloud/aliyun/tencent
            $table->string('content_url', 500)->nullable()->after('content_preview'); // 媒体资源URL
        });
    }

    public function down(): void
    {
        Schema::table('content_audit_logs', function (Blueprint $table) {
            $table->dropColumn(['provider', 'content_url']);
        });

        Schema::table('identity_verifications', function (Blueprint $table) {
            $table->dropColumn('provider');
        });
    }
};
