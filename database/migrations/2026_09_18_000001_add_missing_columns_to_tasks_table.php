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
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('parent_task_id');
            }
            if (!Schema::hasColumn('tasks', 'board_column')) {
                $table->integer('board_column')->default(0)->after('category_id');
            }
            if (!Schema::hasColumn('tasks', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('board_column');
            }
            if (!Schema::hasColumn('tasks', 'estimate_hours')) {
                $table->decimal('estimate_hours', 6, 2)->nullable()->after('due_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $columns = ['category_id', 'board_column', 'sort_order', 'estimate_hours'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('tasks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
