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
        Schema::create('chat_participants', function (Blueprint $table) {
            $table->unsignedBigInteger("chat_id");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("last_read_message_id")->nullable();
            $table->timestamps();
            $table->primary(["chat_id","user_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_participants');
    }
};
