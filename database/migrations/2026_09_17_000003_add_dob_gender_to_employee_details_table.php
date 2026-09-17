<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_details', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('last_date');
            }
            if (!Schema::hasColumn('employee_details', 'gender')) {
                $table->string('gender', 10)->nullable()->after('date_of_birth');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('employee_details', 'date_of_birth')) {
                $cols[] = 'date_of_birth';
            }
            if (Schema::hasColumn('employee_details', 'gender')) {
                $cols[] = 'gender';
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
