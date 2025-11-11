<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Client_Information', function (Blueprint $table) {
            $table->string('password')->nullable()->after('ClientNo');
            $table->string('phone')->nullable()->after('password');
            $table->boolean('is_phone_verified')->default(false)->after('phone');
            $table->string('phone_verification_code')->nullable()->after('is_phone_verified');
        });
    }

    public function down(): void
    {
        Schema::table('Client_Information', function (Blueprint $table) {
            $table->dropColumn(['password', 'phone', 'is_phone_verified', 'phone_verification_code']);
        });
    }
};
