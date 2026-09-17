<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'channel_id')) {
                $table->unsignedBigInteger('channel_id')->nullable()->after('client_id');
            }
            if (!Schema::hasColumn('tickets', 'type_id')) {
                $table->unsignedBigInteger('type_id')->nullable()->after('channel_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('tickets', 'channel_id')) {
                $cols[] = 'channel_id';
            }
            if (Schema::hasColumn('tickets', 'type_id')) {
                $cols[] = 'type_id';
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
