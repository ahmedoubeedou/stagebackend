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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('brand', 80);
            $table->string('model', 80);
            $table->year('year');
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('mileage');
            $table->enum('fuel_type', ['gasoline', 'diesel', 'electric']);
            $table->enum('transmission', ['automatic', 'manual']);
            $table->string('color', 40)->nullable();
            $table->text('description')->nullable();
            $table->string('location', 100)->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->enum('status', ['available', 'sold', 'hidden'])->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};