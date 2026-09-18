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
        Schema::table('contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('contracts', 'amount')) {
                $table->decimal('amount', 16, 2)->nullable()->after('value');
            }
            if (!Schema::hasColumn('contracts', 'currency_id')) {
                $table->unsignedBigInteger('currency_id')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('contracts', 'contract_detail')) {
                $table->longText('contract_detail')->nullable()->after('description');
            }
            if (!Schema::hasColumn('contracts', 'contract_number')) {
                $table->string('contract_number')->nullable()->after('hash');
            }
            if (!Schema::hasColumn('contracts', 'original_contract_number')) {
                $table->string('original_contract_number')->nullable()->after('contract_number');
            }
            if (!Schema::hasColumn('contracts', 'project_id')) {
                $table->unsignedBigInteger('project_id')->nullable()->after('client_id');
            }
            if (!Schema::hasColumn('contracts', 'company_address_id')) {
                $table->unsignedBigInteger('company_address_id')->nullable()->after('project_id');
            }
            if (!Schema::hasColumn('contracts', 'company_sign')) {
                $table->text('company_sign')->nullable()->after('signature');
            }
            if (!Schema::hasColumn('contracts', 'sign_date')) {
                $table->date('sign_date')->nullable()->after('company_sign');
            }
            if (!Schema::hasColumn('contracts', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('signed_on');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $columns = [
                'amount', 'currency_id', 'contract_detail', 'contract_number',
                'original_contract_number', 'project_id', 'company_address_id',
                'company_sign', 'sign_date', 'created_by',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('contracts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
