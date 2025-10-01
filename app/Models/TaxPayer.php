<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxPayer extends Model
{
    protected $table = 'Client_Information';
    protected $primaryKey = 'ClientNo';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    /** @use HasFactory<\Database\Factories\TaxPayerFactory> */
    use HasFactory;

    protected $guarded = [];

    // Relationship: TaxPayer belongsTo Street

    public function street()
    {
        return $this->belongsTo(Street::class, 'StreetID', 'StreetID');
    }

    public function TblPropType()
    {
        return $this->belongsTo(TblPropType::class, 'PropTypeID', 'PropTypeID');
    }
    public function PropUseID()
    {
        return $this->belongsTo(TblPropUseID::class, 'PropUseID', 'PropUseID');
    }
    public function TaxpayerType()
    {
        return $this->belongsTo(TaxpayerType::class, 'TaxpayerTypeID', 'TaxpayerTypeID');
    }
    public function BankAcc()
    {
        return $this->belongsTo(BankAcc::class, 'BankNo', 'BankNo');
    }
}
