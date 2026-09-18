<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->string('channel', 20)->default('database'); // database, mail, sms, dingtalk, wework, feishu
            $table->string('type', 50); // task_assigned, leave_request, etc.
            $table->boolean('enabled')->default(true);
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            $table->boolean('sound_enabled')->default(true);
            $table->boolean('desktop_enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'type', 'channel'], 'notif_setting_unique');
            $table->index('company_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
