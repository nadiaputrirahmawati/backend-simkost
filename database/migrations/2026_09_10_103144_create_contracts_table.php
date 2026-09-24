<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('monthly_price', 15, 2);
            $table->decimal('deposit_amount', 15, 2)->default(0);
            $table->enum('status', ['pending_payment', 'active', 'in_renewal', 'completed', 'cancelled'])->default('pending_payment');
            $table->enum('contract_type', ['initial', 'renewal'])->default('initial');
            $table->enum('verification_contract', ['pending', 'completed', 'rejected'])->default('pending');
            $table->text('signature_user')->nullable();
            $table->text('signature_owner')->nullable();
            $table->text('rejection_feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
