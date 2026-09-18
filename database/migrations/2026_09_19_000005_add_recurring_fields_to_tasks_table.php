<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('board_column');
            $table->unsignedInteger('recurring_every')->nullable()->after('is_recurring');
            $table->string('recurring_type', 20)->nullable()->after('recurring_every');
            $table->date('recurring_until')->nullable()->after('recurring_type');
            $table->date('recurring_next_date')->nullable()->after('recurring_until');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['is_recurring', 'recurring_every', 'recurring_type', 'recurring_until', 'recurring_next_date']);
        });
    }
};
