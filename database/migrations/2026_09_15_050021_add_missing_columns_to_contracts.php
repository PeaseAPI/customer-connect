<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('contracts', 'amount')) {
                $table->decimal('amount', 16, 2)->default(0)->after('subject');
            }
            if (!Schema::hasColumn('contracts', 'currency_id')) {
                $table->unsignedBigInteger('currency_id')->nullable()->after('contract_type_id');
            }
            if (!Schema::hasColumn('contracts', 'contract_detail')) {
                $table->longText('contract_detail')->nullable()->after('description');
            }
            if (!Schema::hasColumn('contracts', 'contract_number')) {
                $table->string('contract_number')->nullable()->after('id');
            }
            if (!Schema::hasColumn('contracts', 'original_contract_number')) {
                $table->string('original_contract_number')->nullable()->after('contract_number');
            }
            if (!Schema::hasColumn('contracts', 'project_id')) {
                $table->unsignedBigInteger('project_id')->nullable()->after('client_id');
            }
            if (!Schema::hasColumn('contracts', 'company_address_id')) {
                $table->unsignedBigInteger('company_address_id')->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('contracts', 'company_sign')) {
                $table->string('company_sign')->nullable()->after('signature');
            }
            if (!Schema::hasColumn('contracts', 'sign_date')) {
                $table->date('sign_date')->nullable()->after('signed_on');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $cols = ['amount', 'currency_id', 'contract_detail', 'contract_number', 'original_contract_number', 'project_id', 'company_address_id', 'company_sign', 'sign_date'];
            foreach ($cols as $c) { if (Schema::hasColumn('contracts', $c)) $table->dropColumn($c); }
        });
    }
};
