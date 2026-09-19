<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 手机号实名认证记录
        Schema::create('identity_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('verifiable'); // verifiable_type + verifiable_id
            $table->string('type'); // two_factor / three_factor
            $table->string('name');
            $table->string('phone', 20);
            $table->string('id_number', 20)->nullable();
            $table->string('result', 20); // MATCH / MISMATCH / UNKNOWN
            $table->string('carrier', 10)->nullable(); // CMCC / CUCC / CTCC
            $table->string('request_id')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('result');
            $table->index('phone');
            $table->index('verified_at');
        });

        // 内容审核记录
        Schema::create('content_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('auditable'); // auditable_type + auditable_id
            $table->string('content_type', 20); // text / image / video / audio
            $table->text('content_preview')->nullable();
            $table->string('task_id')->nullable();
            $table->string('suggestion', 20)->default('PENDING'); // PASS / REVIEW / BLOCK / PENDING
            $table->string('risk_level', 10)->default('NONE'); // NONE / LOW / MEDIUM / HIGH
            $table->json('labels')->nullable();
            $table->json('details')->nullable();
            $table->string('status', 20)->default('PROCESSING'); // PROCESSING / COMPLETED / FAILED
            $table->timestamp('audited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('content_type');
            $table->index('suggestion');
            $table->index('status');
            $table->index('task_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_audit_logs');
        Schema::dropIfExists('identity_verifications');
    }
};
