<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TblPropUseID extends Model
{
    protected $table = 'Tbl_PropUseID';
    protected $primaryKey = 'PropUseID';
    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = false;
    /** @use HasFactory<\Database\Factories\TblPropUseIDFactory> */
    use HasFactory;
    protected $guarded = [];

    public function taxPayers()
    {
        return $this->hasMany(TblPropType::class, 'PropUseID', 'PropUseID');
    }
}
