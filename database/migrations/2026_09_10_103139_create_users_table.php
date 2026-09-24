<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->enum('role', ['admin', 'owner', 'user'])->default('user');
            $table->string('phone_number', 20)->nullable();
            $table->string('no_ktp', 20)->unique()->nullable();
            $table->string('npwp', 25)->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->enum('work', ['bekerja', 'mahasiswa', 'lainnya'])->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->text('address')->nullable();
            $table->enum('marital_status', ['menikah', 'belum_menikah'])->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_holder', 255)->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('profile_picture', 255)->nullable();
            $table->string('ktp_picture', 255)->nullable();
            $table->string('ktp_picture_person', 255)->nullable();
            $table->enum('status_verification', ['unverified', 'pending', 'verified', 'rejected'])->default('unverified');
            $table->text('rejection_feedback')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
