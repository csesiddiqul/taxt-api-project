<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('BankAcc', function (Blueprint $table) {
            $table->longText(column: 'BankNo')->primary();
            $table->string('BankName')->nullable();
            $table->string('Branch')->nullable();
            $table->string('AccountsNo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('BankAcc');
    }
};
