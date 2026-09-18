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
            if (!Schema::hasColumn('client_details', 'company_name')) {
                $table->string('company_name')->nullable()->after('sub_category_id');
            }
            if (!Schema::hasColumn('client_details', 'website')) {
                $table->string('website')->nullable()->after('shipping_address');
            }
            if (!Schema::hasColumn('client_details', 'skype')) {
                $table->string('skype')->nullable()->after('website');
            }
            if (!Schema::hasColumn('client_details', 'linkedin')) {
                $table->string('linkedin')->nullable()->after('skype');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
        public function down(): void
    {
        Schema::table('client_details', function (Blueprint $table) {
            $columns = ['company_name', 'website', 'skype', 'linkedin'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('client_details', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
