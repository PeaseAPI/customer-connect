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
        Schema::create('approval_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("request_id");
            $table->unsignedInteger("approver_id");
            $table->unsignedInteger("step");
            $table->string("action",20);
            $table->text("remark")->nullable();
            $table->boolean("external_action")->default(false);
            $table->timestamp("acted_at")->nullable();
            $table->index("request_id");
            $table->index("approver_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_records');
    }
};
