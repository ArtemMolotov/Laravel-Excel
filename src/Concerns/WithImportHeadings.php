<?php

namespace Maatwebsite\Excel\Concerns;

use Illuminate\Support\Collection;

interface WithImportHeadings
{
    /**
     * @var array|Collection $headings
     * @return void
     */
    public function setHeadings($headings);
}
