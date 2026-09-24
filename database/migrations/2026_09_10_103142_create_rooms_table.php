<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kost_id')->constrained('kosts')->cascadeOnDelete();
            $table->string('room_number', 50);
            $table->string('room_size', 20)->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('deposit_amount', 15, 2)->default(0);
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available');
            $table->json('room_facility')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
