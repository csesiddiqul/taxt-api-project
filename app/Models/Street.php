<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Street extends Model
{
    protected $table = 'Street';
    protected $primaryKey = 'StreetID';
    protected $keyType = 'string';
    public $timestamps = false;
    public $incrementing = false;
    /** @use HasFactory<\Database\Factories\StreetFactory> */
    use HasFactory;
    protected $guarded = [];

    // Relationship: Street hasMany TaxPayer
    public function taxPayers()
    {
        return $this->hasMany(TaxPayer::class, 'StreetID', 'StreetID');
    }
}
