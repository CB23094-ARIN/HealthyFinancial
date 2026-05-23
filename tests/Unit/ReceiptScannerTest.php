<?php

namespace Tests\Unit;

use App\Services\ReceiptScanner;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

class ReceiptScannerTest extends TestCase
{
    public function test_it_extracts_simple_receipt_items_without_ai(): void
    {
        $scanner = new ReceiptScanner();
        $method = (new ReflectionClass($scanner))->getMethod('extractItemsLocally');
        $method->setAccessible(true);

        $items = $method->invoke($scanner, 'Nasi Lemak RM5.50, Teh O RM2.00, Total RM7.50');

        $this->assertCount(2, $items);
        $this->assertSame('Nasi Lemak', $items[0]['description']);
        $this->assertSame(5.50, $items[0]['amount']);
        $this->assertSame('food', $items[0]['category']);
    }

    public function test_it_extracts_priced_receipt_lines_and_ignores_totals(): void
    {
        $scanner = new ReceiptScanner();
        $method = (new ReflectionClass($scanner))->getMethod('extractItemsLocally');
        $method->setAccessible(true);

        $ocrText = <<<TEXT
        INVOICE No : 10102_01/030218
        Date : 21/05/2026 #1
        QTY ITEM RM
        *** Dine In ***
        2 Set A @13.90 27.80
          2 Ayam Bumbu Ori
          1 Co*
          2 Sambal Pedas
          2 Iced Lemon Tea
        2 SubTotal 27.80
        Net Total 27.80
        MyDebit 27,80
        TEXT;

        $items = $method->invoke($scanner, $ocrText);

        $this->assertCount(1, $items);
        $this->assertSame('Set A', $items[0]['description']);
        $this->assertSame(27.80, $items[0]['amount']);
        $this->assertSame('food', $items[0]['category']);
    }

    public function test_it_uses_configured_tesseract_path_when_present(): void
    {
        $fakeExecutable = __DIR__ . DIRECTORY_SEPARATOR . 'fake-tesseract.exe';
        $previousPath = getenv('TESSERACT_PATH');

        file_put_contents($fakeExecutable, '');
        putenv("TESSERACT_PATH={$fakeExecutable}");

        try {
            $scanner = new ReceiptScanner();
            $method = (new ReflectionClass($scanner))->getMethod('resolveTesseractExecutable');
            $method->setAccessible(true);

            $this->assertSame($fakeExecutable, $method->invoke($scanner));
        } finally {
            $previousPath === false
                ? putenv('TESSERACT_PATH')
                : putenv("TESSERACT_PATH={$previousPath}");

            @unlink($fakeExecutable);
        }
    }

    public function test_it_reports_invalid_configured_tesseract_path(): void
    {
        $previousPath = getenv('TESSERACT_PATH');
        $missingPath = __DIR__ . DIRECTORY_SEPARATOR . 'missing-tesseract.exe';

        putenv("TESSERACT_PATH={$missingPath}");

        try {
            $scanner = new ReceiptScanner();
            $method = (new ReflectionClass($scanner))->getMethod('resolveTesseractExecutable');
            $method->setAccessible(true);

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Configured TESSERACT_PATH was not found');

            $method->invoke($scanner);
        } finally {
            $previousPath === false
                ? putenv('TESSERACT_PATH')
                : putenv("TESSERACT_PATH={$previousPath}");
        }
    }
}
