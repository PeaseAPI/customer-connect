<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_settings',function(Blueprint $table){
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('copyright_text')->nullable();
            $table->text('footer_note')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('footer_settings');
    }
};
