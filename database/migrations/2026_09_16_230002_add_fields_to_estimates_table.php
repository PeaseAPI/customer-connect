<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->string('discount_type', 10)->default('percent')->after('discount');
            $table->date('sent_on')->nullable()->after('hash');
            $table->unsignedBigInteger('last_updated_by')->nullable()->after('created_by');

            $table->foreign('last_updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropForeign(['last_updated_by']);
            $table->dropColumn(['discount_type', 'sent_on', 'last_updated_by']);
        });
    }
};
