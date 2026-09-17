<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->string('mobile', 20)->nullable()->after('email');
            $table->string('image')->nullable()->after('password');
            $table->string('gender', 10)->default('other')->after('image');
            $table->string('locale', 10)->default('zh-cn')->after('gender');
            $table->string('status', 20)->default('active')->after('locale');
            $table->string('login', 20)->default('enable')->after('status');
            $table->timestamp('last_login')->nullable()->after('login');
            $table->boolean('email_notifications')->default(true)->after('last_login');
            $table->unsignedInteger('country_id')->nullable()->after('email_notifications');
            $table->boolean('two_factor_enabled')->default(false)->after('country_id');
            $table->text('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->string('wechat_openid', 100)->nullable()->after('two_factor_secret');
            $table->string('wechat_unionid', 100)->nullable()->after('wechat_openid');
            $table->string('dingtalk_userid', 100)->nullable()->after('wechat_unionid');
            $table->string('wework_userid', 100)->nullable()->after('dingtalk_userid');
            $table->string('feishu_userid', 100)->nullable()->after('wework_userid');
            $table->softDeletes();
            $table->index('company_id');
            $table->index('mobile');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'company_id', 'mobile', 'image', 'gender', 'locale',
                'status', 'login', 'last_login', 'email_notifications',
                'country_id', 'two_factor_enabled', 'two_factor_secret',
                'wechat_openid', 'wechat_unionid', 'dingtalk_userid',
                'wework_userid', 'feishu_userid', 'deleted_at',
            ]);
        });
    }
};
