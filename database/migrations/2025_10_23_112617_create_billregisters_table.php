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
        Schema::create('billregisters', function (Blueprint $table) {
            $table->id();
            $table->string('ClientNo');
            $table->string('Year')->nullable();
            $table->string('Year1')->nullable();
            $table->string('Period_of_Bill')->nullable();
            $table->date('DateOfIssue')->nullable();
            $table->date('LastPaymentDate')->nullable();
            $table->string('ArrStYear')->nullable();
            $table->string('ArrStYear1')->nullable();
            $table->string('ArrStPeriod')->nullable();

            $table->decimal('HoldingTax', 10, 2)->default(0);
            $table->decimal('LightTax', 10, 2)->default(0);
            $table->decimal('ConserTax', 10, 2)->default(0);
            $table->decimal('WaterTax', 10, 2)->default(0);

            $table->decimal('Q1', 10, 2)->default(0);
            $table->decimal('Q2', 10, 2)->default(0);
            $table->decimal('Q3', 10, 2)->default(0);
            $table->decimal('Q4', 10, 2)->default(0);

            $table->decimal('CurrentChearge', 10, 2)->default(0);
            $table->decimal('CurrentChearge2', 10, 2)->default(0);
            $table->decimal('CurrentChearge3', 10, 2)->default(0);
            $table->decimal('CurrentChearge4', 10, 2)->default(0);

            $table->decimal('1QRebate', 10, 2)->default(0);
            $table->decimal('2QRebate', 10, 2)->default(0);
            $table->decimal('3QRebate', 10, 2)->default(0);
            $table->decimal('4QRebate', 10, 2)->default(0);

            $table->decimal('YArear', 10, 2)->default(0);
            $table->decimal('Surcharge', 10, 2)->default(0);
            $table->decimal('PartArrPay', 10, 2)->default(0);
            $table->decimal('PartSur', 10, 2)->default(0);
            $table->decimal('BillPaid', 10, 2)->default(0);

            $table->date('Paid_Date')->nullable();
            $table->string('TaxpayerTypeID')->nullable();
            $table->string('sr')->nullable();
            $table->decimal('PartCurrent', 10, 2)->default(0);
            $table->date('PartPayDate')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billregisters');
    }
};
