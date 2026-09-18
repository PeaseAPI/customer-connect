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
        Schema::table('client_details', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('sub_category_id');
            $table->string('website')->nullable()->after('shipping_address');
            $table->string('skype')->nullable()->after('website');
            $table->string('linkedin')->nullable()->after('skype');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_details', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'website', 'skype', 'linkedin']);
        });
    }
};
