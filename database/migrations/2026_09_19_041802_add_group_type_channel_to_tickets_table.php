<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Per-column guards: 2026_09_17_000001 already added type_id/channel_id,
        // and this dev database got group_id from an earlier partially-failed
        // run. A fresh deployment has none of them.
        Schema::table('tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('tickets', 'group_id')) {
                $table->foreignId('group_id')->nullable()->after('company_id')->constrained('ticket_groups')->nullOnDelete();
            }
            if (! Schema::hasColumn('tickets', 'type_id')) {
                $table->foreignId('type_id')->nullable()->after('group_id')->constrained('ticket_types')->nullOnDelete();
            }
            if (! Schema::hasColumn('tickets', 'channel_id')) {
                $table->foreignId('channel_id')->nullable()->after('type_id')->constrained('ticket_channels')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropForeign(['type_id']);
            $table->dropForeign(['channel_id']);
            $table->dropColumn(['group_id', 'type_id', 'channel_id']);
        });
    }
};
