<?php

namespace App\Exports;

use App\Models\Street;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StreetsExport implements FromCollection, WithHeadings
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        return Street::when($this->search, function ($q, $search) {
            $q->where('StreetID', 'LIKE', "%{$search}%")
                ->orWhere('StreetName', 'LIKE', "%{$search}%");
        })->select('StreetID', 'StreetName')->get();
    }

    public function headings(): array
    {
        return [
            'Street ID',
            'Street Name',
        ];
    }
}
