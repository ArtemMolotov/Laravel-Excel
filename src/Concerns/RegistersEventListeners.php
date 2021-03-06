<?php

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\BeforeReading;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;

trait RegistersEventListeners
{
    /**
     * @return array
     */
    public function registerEvents(): array
    {
        $listeners = [];

        if (method_exists($this, 'beforeImport')) {
            $listeners[BeforeImport::class] = [static::class, 'beforeImport'];
        }

        if (method_exists($this, 'beforeReading')) {
            $listeners[BeforeReading::class] = [static::class, 'beforeReading'];
        }

        if (method_exists($this, 'beforeExport')) {
            $listeners[BeforeExport::class] = [static::class, 'beforeExport'];
        }

        if (method_exists($this, 'beforeWriting')) {
            $listeners[BeforeWriting::class] = [static::class, 'beforeWriting'];
        }

        if (method_exists($this, 'beforeSheet')) {
            $listeners[BeforeSheet::class] = [static::class, 'beforeSheet'];
        }

        if (method_exists($this, 'afterSheet')) {
            $listeners[AfterSheet::class] = [static::class, 'afterSheet'];
        }

        return $listeners;
    }
}
