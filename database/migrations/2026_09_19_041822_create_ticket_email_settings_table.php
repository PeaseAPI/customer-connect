<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_email_settings',function(Blueprint $table){
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('imap_host')->nullable();
            $table->integer('imap_port')->default(0);
            $table->string('imap_username')->nullable();
            $table->string('imap_password')->nullable();
            $table->string('imap_encryption')->nullable();
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('ticket_email_settings');
    }
};
