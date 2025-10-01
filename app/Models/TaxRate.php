<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{


    protected $table = 'TaxRates';
    protected $primaryKey = 'Id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;


    /** @use HasFactory<\Database\Factories\TaxRateFactory> */
    use HasFactory;

    protected $guarded = [];
}
