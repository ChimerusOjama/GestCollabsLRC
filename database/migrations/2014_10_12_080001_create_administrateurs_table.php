<?php
// database/migrations/2014_10_12_080001_create_administrateurs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administrateurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('phone')->nullable();
            $table->string('department')->default('Administration');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrateurs');
    }
};