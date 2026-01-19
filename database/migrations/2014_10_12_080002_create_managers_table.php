<?php
// database/migrations/2014_10_12_080002_create_managers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('managers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('phone')->nullable();
            $table->string('department');
            $table->enum('level', ['junior', 'senior', 'director'])->default('junior');
            $table->timestamps();

            $table->index(['department', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('managers');
    }
};