<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAcc extends Model
{
    protected $table = 'BankAcc';
    protected $primaryKey = 'BankNo';
    protected $keyType = 'string';
    public $timestamps = false;
    public $incrementing = false;
    /** @use HasFactory<\Database\Factories\BankAccFactory> */

    use HasFactory;
    protected $guarded = [];

    use HasFactory;

    public function taxPayers()
    {
        return $this->hasMany(TblPropType::class, 'BankNo', 'BankNo');
    }
}
