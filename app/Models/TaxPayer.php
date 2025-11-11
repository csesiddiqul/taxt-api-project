<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class TaxPayer extends Authenticatable
{
    use HasApiTokens, HasFactory;
    protected $table = 'Client_Information';
    protected $primaryKey = 'ClientNo';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;


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

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
