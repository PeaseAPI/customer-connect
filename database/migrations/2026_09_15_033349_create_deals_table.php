<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('pipeline_stage_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->integer('column_priority')->default(0);
            $table->string('company_name')->nullable();
            $table->string('deal_name');
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->string('salutation')->nullable();
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('cell', 30)->nullable();
            $table->string('office', 30)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->text('note')->nullable();
            $table->dateTime('next_follow_up')->nullable();
            $table->decimal('value', 16, 2)->nullable();
            $table->decimal('total_value', 16, 2)->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('last_updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('pipeline_stage_id')->references('id')->on('pipeline_stages')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
