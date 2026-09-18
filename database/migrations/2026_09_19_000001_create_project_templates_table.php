<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('template_name');
            $table->text('description')->nullable();
            $table->json('task_structure')->nullable(); // 模板任务结构
            $table->json('milestone_structure')->nullable(); // 模板里程碑结构
            $table->json('default_member_ids')->nullable(); // 默认成员
            $table->string('category_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_templates');
    }
};
