<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Billregister extends Model
{
    use HasFactory;
    protected $table = 'Bill_Register';
    protected $fillable = [
        'ClientNo',
        'Year',
        'Year1',
        'Period_of_Bill',
        'DateOfIssue',
        'LastPaymentDate',
        'ArrStYear',
        'ArrStYear1',
        'ArrStPeriod',
        'HoldingTax',
        'LightTax',
        'ConserTax',
        'WaterTax',
        'Q1',
        'Q2',
        'Q3',
        'Q4',
        'CurrentChearge',
        'CurrentChearge2',
        'CurrentChearge3',
        'CurrentChearge4',
        '1QRebate',
        '2QRebate',
        '3QRebate',
        '4QRebate',
        'YArear',
        'Surcharge',
        'PartArrPay',
        'PartSur',
        'BillPaid',
        'Paid_Date',
        'TaxpayerTypeID',
        'sr',
        'PartCurrent',
        'PartPayDate',
    ];

    /**
     * Relationship: A bill belongs to a taxpayer (client)
     */
    public function taxpayer()
    {
        return $this->belongsTo(TaxPayer::class, 'ClientNo', 'ClientNo');
    }
}
