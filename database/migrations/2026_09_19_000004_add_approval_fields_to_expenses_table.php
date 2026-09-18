<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('approved_by')->nullable()->after('created_by');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('approval_remark', 500)->nullable()->after('approved_at');

            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approved_by', 'approved_at', 'approval_remark']);
        });
    }
};
