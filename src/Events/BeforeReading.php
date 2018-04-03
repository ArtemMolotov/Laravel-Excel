<?php

namespace Maatwebsite\Excel\Events;

use Maatwebsite\Excel\Reader;

class BeforeReading
{
    /**
     * @var Reader
     */
    public $reader;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @return Reader
     */
    public function getReader(): Reader
    {
        return $this->reader;
    }
}
