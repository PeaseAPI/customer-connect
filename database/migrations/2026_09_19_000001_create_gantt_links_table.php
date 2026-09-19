<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gantt_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('target_task_id')->constrained('tasks')->cascadeOnDelete();
            $table->tinyInteger('type')->default(0)->comment('0=FS,1=SS,2=FF,3=SF');
            $table->integer('lag')->default(0);
            $table->timestamps();

            $table->index(['source_task_id', 'target_task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gantt_links');
    }
};
