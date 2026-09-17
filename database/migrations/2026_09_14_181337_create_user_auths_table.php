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
        Schema::create('user_auths', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
$table->unsignedBigInteger("company_id");
$table->boolean("is_superadmin")->default(false);
$table->timestamp("last_login")->nullable();
$table->unique(["user_id","company_id"]);
$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_auths');
    }
};
