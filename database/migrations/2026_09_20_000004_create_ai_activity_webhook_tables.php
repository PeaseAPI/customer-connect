<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // AI 助手对话表
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('title')->default('New conversation');
            $table->string('model', 50)->default('gpt-3.5-turbo');
            $table->json('context')->nullable(); // 上下文信息（当前模块、实体等）
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // AI 对话消息表
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ai_conversation_id')->index();
            $table->enum('role', ['system', 'user', 'assistant']);
            $table->text('content');
            $table->json('metadata')->nullable(); // token usage, model info 等
            $table->timestamps();

            $table->foreign('ai_conversation_id')->references('id')->on('ai_conversations')->cascadeOnDelete();
        });

        // Activity log表
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('log_name', 50)->index(); // 模块名如 crm, pm, finance 等
            $table->string('description');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('event', 30)->nullable(); // created, updated, deleted, etc.
            $table->json('properties')->nullable(); // 变更详情
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
        });

        // Webhook 表
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('name');
            $table->string('url');
            $table->string('secret', 64)->nullable(); // 用于签名验证
            $table->json('events'); // 订阅的Event type
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('failure_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Webhook 投递记录
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('webhook_id')->index();
            $table->string('event');
            $table->json('payload');
            $table->integer('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->string('status', 20)->default('pending'); // pending, success, failed
            $table->timestamps();

            $table->foreign('webhook_id')->references('id')->on('webhooks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }
};
