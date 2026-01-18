<?php
// database/migrations/2014_10_12_080003_create_collaborateurs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaborateurs', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique(); // Email professionnel
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('department');
            $table->string('poste');
            $table->date('date_embauche');
            $table->enum('statut', ['actif', 'inactif', 'congé', 'licencié'])->default('actif');
            $table->decimal('salaire', 10, 2)->nullable();
            $table->json('competences')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('managers')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['department', 'statut']);
            $table->index('matricule');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaborateurs');
    }
};