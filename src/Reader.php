<?php

namespace Maatwebsite\Excel;

use Maatwebsite\Excel\Concerns\WithImportTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\BeforeReading;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Filesystem\FilesystemManager;
use Maatwebsite\Excel\Concerns\WithEvents;

class Reader
{
    use DelegatedMacroable, HasEventBus;

    /**
     * @var Spreadsheet
     */
    protected $spreadsheet;

    /**
     * @var FilesystemManager
     */
    private $filesystem;

    /**
     * @var string
     */
    protected $tmpPath;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var IReader
     */
    protected $reader;

    /**
     * @var int
     */
    protected $chunkSize;

    /**
     * @param FilesystemManager $filesystem
     */
    public function __construct(FilesystemManager $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->chunkSize  = config('excel.exports.chunk_size', 1000);
        $this->tmpPath    = config('excel.exports.temp_path', sys_get_temp_dir());
    }

    /**
     * @param object           $import
     * @param string           $filePath
     * @param string|null      $disk
     * @param string|null      $readerType
     * @param IReadFilter|null $filter
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     * @return bool
     */
    public function import($import, string $filePath, $disk = null, $readerType = null, IReadFilter $filter = null): bool
    {
        $this->open($import,$filePath,$disk);
        $readerType = $readerType ?? IOFactory::identify($this->fileName);

        $sheetImports = [$import];
        if ($import instanceof WithMultipleSheets) {
            $sheetImports = $import->sheets();
        }

        $this->raise(new BeforeReading($this));

        //for($i = 0; $i < $chunk; $i++){
            $this->read($this->fileName, $readerType, $filter);
            foreach ($sheetImports as $index => $sheetImport) {
                $this->getSheetByIndex($index)->import($sheetImport);
            }
        //}

        unlink($this->fileName);

        return true;
    }

    /**
     * @param object      $import
     * @param string      $filePath
     * @param string|null $disk
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     * @return $this
     */
    public function open($import, string $filePath, $disk = null)
    {
        if ($import instanceof WithEvents) {
            $this->registerListeners($import->registerEvents());
        }

        // --------------------------- Copy file to temporary file ---------------------------
        $file = $this->filesystem->disk($disk)->get($filePath);
        $tmpFile = $this->tempFile();

        file_put_contents($tmpFile, $file);
        $this->fileName = $tmpFile;
        // --------------------------- Copy file to temporary file ---------------------------

        $this->raise(new BeforeImport($this));

        if ($import instanceof WithImportTitle) {
            $import->setTitle($this->spreadsheet->getProperties()->getTitle());
        }

        return $this;
    }

    /**
     * @param string           $readerType
     * @param IReadFilter|null $filter
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     *
     * @return IReader
     */
    public function initRead(string $readerType, IReadFilter $filter = null)
    {
        $reader = IOFactory::createReader($readerType);
        $reader->setReadDataOnly(true);
        if ($filter){
            $reader->setReadFilter($filter);
        }

        $this->reader = $reader;
        return $this->reader;
    }

    /**
     * @param string           $fileName
     * @param string|null      $readerType
     * @param IReadFilter|null $filter
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     *
     * @return string
     */
    public function read(string $fileName, string $readerType = null, IReadFilter $filter = null)
    {
        if (!$this->reader instanceof IReader) {
            $this->initRead($readerType, $filter);
        }

        if (!$this->reader->canRead($fileName)) {
            dd('nope');
        }

        $this->spreadsheet = $this->reader->load($fileName);
        return $fileName;
    }

    /**
     * @return object
     */
    public function getDelegate()
    {
        return $this->spreadsheet;
    }

    /**
     * @return string
     */
    public function tempFile(): string
    {
        return tempnam($this->tmpPath, 'laravel-excel-import');
    }

    /**
     * @param int $sheetIndex
     * @return Sheet
     */
    public function getSheetByIndex(int $sheetIndex)
    {
        return new Sheet($this->getDelegate()->getSheet($sheetIndex));
    }
}
