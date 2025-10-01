<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblPropType extends Model
{


    protected $table = 'Tbl_PropType';
    protected $primaryKey = 'PropTypeID';
    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = false;
    /** @use HasFactory<\Database\Factories\TblPropTypeFactory> */
    use HasFactory;
    protected $guarded = [];

    public function taxPayers()
    {
        return $this->hasMany(TblPropType::class, 'PropTypeID', 'PropTypeID');
    }
}
