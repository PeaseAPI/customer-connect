<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('default_driver', 30)->default('local');
            $table->integer('max_file_size')->default(10240);
            $table->json('allowed_types')->nullable();
            $table->string('s3_key')->nullable();
            $table->string('s3_secret')->nullable();
            $table->string('s3_region')->nullable();
            $table->string('s3_bucket')->nullable();
            $table->string('s3_url')->nullable();
            $table->string('oss_access_key_id')->nullable();
            $table->string('oss_access_key_secret')->nullable();
            $table->string('oss_endpoint')->nullable();
            $table->string('oss_bucket')->nullable();
            $table->string('oss_url')->nullable();
            $table->boolean('oss_is_cname')->default(false);
            $table->string('cos_app_id')->nullable();
            $table->string('cos_secret_id')->nullable();
            $table->string('cos_secret_key')->nullable();
            $table->string('cos_region')->nullable();
            $table->string('cos_bucket')->nullable();
            $table->string('cos_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('database_backups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('filename');
            $table->string('path');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('status', 20)->default('pending');
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_backups');
        Schema::dropIfExists('storage_settings');
    }
};
