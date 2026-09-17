<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_renew_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('last_updated_by')->nullable()->after('added_by');

            $table->foreign('last_updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contract_renew_histories', function (Blueprint $table) {
            $table->dropForeign(['last_updated_by']);
            $table->dropColumn(['last_updated_by']);
        });
    }
};
