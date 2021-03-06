<?php

namespace Maatwebsite\Excel;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Contracts\Routing\ResponseFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class Excel implements Exporter, Importer
{
    const XLSX     = 'Xlsx';

    const CSV      = 'Csv';

    const ODS      = 'Ods';

    const XLS      = 'Xls';

    const SLK      = 'Slk';

    const XML      = 'Xml';

    const GNUMERIC = 'Gnumeric';

    const HTML     = 'Html';

    const MPDF     = 'Mpdf';

    const DOMPDF   = 'Dompdf';

    const TCPDF    = 'Tcpdf';

    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @var QueuedWriter
     */
    protected $queuedWriter;

    /**
     * @var ResponseFactory
     */
    protected $response;

    /**
     * @var FilesystemManager
     */
    protected $filesystem;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param Writer            $writer
     * @param QueuedWriter      $queuedWriter
     * @param Reader            $reader
     * @param ResponseFactory   $response
     * @param FilesystemManager $filesystem
     */
    public function __construct(
        Writer $writer,
        QueuedWriter $queuedWriter,
        Reader $reader,
        ResponseFactory $response,
        FilesystemManager $filesystem
    ) {
        $this->writer       = $writer;
        $this->reader       = $reader;
        $this->response     = $response;
        $this->filesystem   = $filesystem;
        $this->queuedWriter = $queuedWriter;
    }

    /**
     * {@inheritdoc}
     */
    public function download($export, string $fileName, string $writerType = null)
    {
        $file = $this->export($export, $fileName, $writerType);

        return $this->response->download($file, $fileName);
    }

    /**
     * {@inheritdoc}
     */
    public function store($export, string $filePath, string $disk = null, string $writerType = null)
    {
        if ($export instanceof ShouldQueue) {
            return $this->queue($export, $filePath, $disk, $writerType);
        }

        $file = $this->export($export, $filePath, $writerType);

        return $this->filesystem->disk($disk)->put($filePath, fopen($file, 'r+'));
    }

    /**
     * {@inheritdoc}
     */
    public function queue($export, string $filePath, string $disk = null, string $writerType = null)
    {
        if (null === $writerType) {
            $writerType = $this->findTypeByExtension($filePath);
        }

        return $this->queuedWriter->store($export, $filePath, $disk, $writerType);
    }

    /**
     * {@inheritdoc}
     */
    public function import($import, string $filePath, string $disk = null, string $readerType = null, IReadFilter $filter = null)
    {
        if (null === $readerType) {
            $readerType = $this->findTypeByExtension(basename($filePath));
        }

        return $this->reader->import(
            $import,
            $filePath,
            $disk,
            $readerType,
            $filter
        );
    }

    /**
     * {@inheritdoc}
     */
    public function queuedImport($import, string $filePath, string $disk = null, string $readerType = null)
    {
        // TODO
    }

    /**
     * @param object      $export
     * @param string|null $fileName
     * @param string      $writerType
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @return string
     */
    protected function export($export, string $fileName, string $writerType = null)
    {
        if (null === $writerType) {
            $writerType = $this->findTypeByExtension($fileName);
        }

        return $this->writer->export($export, $writerType);
    }

    /**
     * @param string $fileName
     *
     * @return string|null
     */
    protected function findTypeByExtension(string $fileName)
    {
        $pathInfo = pathinfo($fileName);

        return config('excel.extension_detector.' . strtolower($pathInfo['extension'] ?? ''));
    }
}
