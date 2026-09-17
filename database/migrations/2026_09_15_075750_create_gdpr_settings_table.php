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
        Schema::create('gdpr_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->boolean('gdpr_enable')->default(false);
            $table->text('privacy_policy')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->boolean('show_consent_on_signup')->default(false);
            $table->boolean('allow_right_to_be_forgotten')->default(false);
            $table->boolean('allow_data_export')->default(false);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gdpr_settings');
    }
};
