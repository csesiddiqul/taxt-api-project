<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxpayerType extends Model
{

    protected $table = 'TaxpayerType';
    protected $primaryKey = 'TaxpayerTypeID';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    /** @use HasFactory<\Database\Factories\TaxpayerTypeFactory> */
    use HasFactory;

    protected $guarded = [];

    public function taxPayers()
    {
        return $this->hasMany(TblPropType::class, 'TaxpayerTypeID', 'TaxpayerTypeID');
    }
}
