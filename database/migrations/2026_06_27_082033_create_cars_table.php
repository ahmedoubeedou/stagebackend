<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('brand');
            $table->string('model');
            $table->year('year');

            $table->decimal('price',10,2);

            $table->string('fuel_type')->nullable();
            $table->string('transmission')->nullable();

            $table->integer('mileage')->nullable();
            $table->string('color')->nullable();

            $table->string('condition')->default('used');

            $table->enum('status', [
                'available',
                'sold',
                'hidden'
            ])->default('available');

            $table->text('description')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};