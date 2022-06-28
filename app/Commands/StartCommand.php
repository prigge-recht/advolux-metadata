<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Spatie\PdfToText\Pdf;
use Spatie\Regex\Regex;
use XMLWriter;
use function Termwind\{render};

class StartCommand extends Command
{

    protected $signature = 'start {path=Path of PDF files} {destination=Copy path to final destination if set}';
    protected $description = 'Check folder for PDF files';

    protected array $files;
    protected int $newMetadataFilesCount;
    protected int $filesWithoutMetadataCount;

    public function handle()
    {
        $this->init();
        $this->collectFiles();

        $bar = $this->output->createProgressBar(count($this->files));

        $bar->start();

        foreach ($this->files as $file) {
            $recordNumber = $this->detectMetadata($file);

            if ($recordNumber) {
                $xmlFileName = Str::replaceLast('.pdf', '.xml', $file);
                $this->createDocument($file, $xmlFileName, $recordNumber);

                if ($this->argument('destination')) {
                    copy($this->argument('path') . '/' . $xmlFileName,
                        $this->argument('destination') . '/' . $xmlFileName);

                    unlink($this->argument('path') . '/' . $xmlFileName);

                }
            }

            if ($this->argument('destination')) {
                copy($this->argument('path') . '/' . $file,
                    $this->argument('destination') . '/' . $file);

                unlink($this->argument('path') . '/' . $file);

            }

            $bar->advance();
        }

        $bar->finish();

        $this->showInfo();

    }

    protected function init(): void
    {
        $this->files = [];
        $this->newMetadataFilesCount = 0;
        $this->filesWithoutMetadataCount = 0;
    }

    protected function collectFiles(): void
    {
        $this->task("PDF-Dateien einlesen", function () {
            $this->files = array_diff(scandir($this->argument('path')), array('..', '.'));

            foreach ($this->files as $key => $file) {
                if (!Str::endsWith($file, '.pdf')) {
                    unset($this->files[$key]);
                }
            }
        });
    }


    private function detectMetadata(string $file): ?array
    {
        // Search for ADX_Aktenzeichen_eigen
        $text = Pdf::getText($this->argument('path') . '/' . $file);
        $recordNumbers = Regex::matchAll(config('regex.record-number'), $text)->results();

        if (count($recordNumbers) === 0) {
            $this->filesWithoutMetadataCount = $this->filesWithoutMetadataCount + 1;
            return null;
        }

        $finalRecordNumber = current($recordNumbers)->group(0);

        foreach (config('regex.lawyers') as $lawyer) {
            $finalRecordNumber = Str::replace('/' . $lawyer, '', $finalRecordNumber);
        }

        // Search for ADX_DateiDatum
        $documentDate = Regex::match(config('regex.document-date'), $text)->result();

        if ($documentDate) {
            $documentDate = $this->translateMonth($documentDate);
            $documentDate = Carbon::parse($documentDate)->format('Y-m-d');
        }

        // Search for ADX_EingangDatum
        $creationDate = date("Y-m-d H:i:s", filemtime($this->argument('path') . '/' . $file));

        return [
            "ADX_Aktenzeichen_eigen" => $finalRecordNumber,
            "ADX_DateiDatum" => $documentDate,
            "ADX_EingangDatum" => $creationDate,
        ];
    }

    protected function createDocument(string $file, string $xmlFileName, array $results): void
    {
        $writer = new XMLWriter();
        $writer->openUri($this->argument('path') . '/' . $xmlFileName);
        $writer->setIndent(true);
        $writer->setIndentString('   ');

        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('AdvoluxImport');
        $writer->startElement('Documents');
        $writer->startElement('Document');
        $writer->startAttribute('File');
        $writer->text($file);
        $writer->endAttribute();
        $writer->startElement('Fields');

        foreach ($results as $key => $result) {
            $writer->startElement('Field');
            $writer->startAttribute('Name');
            $writer->text($key);
            $writer->endAttribute();
            $writer->text($result);
            $writer->endElement(); // Field
        }

        $writer->startElement('Field');
        $writer->startAttribute('Name');
        $writer->text('ADX_Dokumenttyp');
        $writer->endAttribute();
        $writer->text('Sonstige');
        $writer->endElement(); // Field

        $writer->endElement(); // Fields
        $writer->endElement(); // Document
        $writer->endElement(); // Documents
        $writer->endElement(); // AdvoluxImport

        $writer->flush();

        $this->newMetadataFilesCount = $this->newMetadataFilesCount + 1;
    }

    private function showInfo()
    {
        print("\n");

        $this->info("Metadaten für PDF-Dateien angelegt: {$this->newMetadataFilesCount}");
        Log::info("Metadaten für PDF-Dateien angelegt: {$this->newMetadataFilesCount}");

        $this->info("PDF-Dateien ohne Metadaten: {$this->filesWithoutMetadataCount}");
        Log::info("PDF-Dateien ohne Metadaten: {$this->filesWithoutMetadataCount}");
    }

    private function translateMonth(string $date) {
        $de = [
            'Januar',
            'Februar',
            'März',
            'Mai',
            'Juni',
            'Juli',
            'August',
            'September',
            'Oktober',
            'November',
            'Dezember'
        ];

        $en = [
            'January',
            'February',
            'March',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        return str_replace($de, $en, $date);
    }

}
