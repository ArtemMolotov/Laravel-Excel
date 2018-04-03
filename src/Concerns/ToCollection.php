<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

interface ToCollection
{
    /**
     * @param array $collection
     * @param Worksheet      $worksheet
     *
     * @return Collection
     */
    public function collection($collection, $worksheet);
}
