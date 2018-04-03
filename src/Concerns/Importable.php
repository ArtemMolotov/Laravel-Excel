<?php

namespace Maatwebsite\Excel\Concerns;

use Maatwebsite\Excel\Excel;
use Illuminate\Foundation\Bus\PendingDispatch;
use Maatwebsite\Excel\Exceptions\NoFilePathGivenException;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

trait Importable
{
    /**
     * @param string      $filePath
     * @param string|null $disk
     * @param string|null $readerType
     * @param IReadFilter|null $filter
     *
     * @throws NoFilePathGivenException
     * @return bool|PendingDispatch
     */
    public function import(string $filePath = null, string $disk = null, string $readerType = null, IReadFilter $filter = null)
    {
        $filePath = $filePath ?? $this->filePath ?? null;

        if (null === $filePath) {
            throw new NoFilePathGivenException();
        }

        return resolve(Excel::class)->import(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $readerType ?? $this->readerType ?? null,
            $filter
        );
    }

    /**
     * @param string|null $filePath
     * @param string|null $disk
     * @param string|null $readerType
     *
     * @throws NoFilePathGivenException
     * @return PendingDispatch
     */
    public function queuedImport(string $filePath = null, string $disk = null, string $readerType = null)
    {
        $filePath = $filePath ?? $this->filePath ?? null;

        if (null === $filePath) {
            throw new NoFilePathGivenException();
        }

        return resolve(Excel::class)->queuedImport(
            $this,
            $filePath,
            $disk ?? $this->disk ?? null,
            $readerType ?? $this->readerType ?? null
        );
    }
}
