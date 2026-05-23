<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class ReceiptScanner
{
    public function scan(UploadedFile $file): array
    {
        $path = $file->store('temp_receipts');
        $fullPath = Storage::path($path);

        try {
            return $this->extractItems($this->readReceiptText($fullPath));
        } finally {
            Storage::delete($path);
        }
    }

    protected function readReceiptText($fullPath): string
    {
        $executable = $this->resolveTesseractExecutable();
        $result = $this->runTesseract($executable, $fullPath, 'msa+eng');

        if ($result['successful']) {
            return trim($result['output']);
        }

        if ($this->isMissingMalayLanguagePack($result['error'])) {
            $fallback = $this->runTesseract($executable, $fullPath, 'eng');

            if ($fallback['successful']) {
                return trim($fallback['output']);
            }

            $result = $fallback;
        }

        throw new RuntimeException('Receipt OCR failed. ' . $this->cleanOcrError($result['error']));
    }

    protected function resolveTesseractExecutable(): string
    {
        $configuredPath = trim((string) env('TESSERACT_PATH'), " \t\n\r\0\x0B'\"");

        if ($configuredPath !== '') {
            if (is_file($configuredPath)) {
                return $configuredPath;
            }

            $configuredExecutable = (new ExecutableFinder())->find($configuredPath);

            if ($configuredExecutable) {
                return $configuredExecutable;
            }

            throw new RuntimeException("Configured TESSERACT_PATH was not found: {$configuredPath}");
        }

        $executable = (new ExecutableFinder())->find('tesseract');

        if ($executable) {
            return $executable;
        }

        foreach ($this->commonWindowsTesseractPaths() as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        throw new RuntimeException(
            'Receipt OCR needs Tesseract installed. Install Tesseract OCR and set TESSERACT_PATH to tesseract.exe in .env, for example C:\Program Files\Tesseract-OCR\tesseract.exe.'
        );
    }

    protected function runTesseract(string $executable, string $imagePath, string $language): array
    {
        $process = new Process([
            $executable,
            $imagePath,
            'stdout',
            '-l',
            $language,
            '--psm',
            '6',
            '-c',
            'preserve_interword_spaces=1',
        ]);

        $process->setTimeout(30);
        $process->run();

        return [
            'successful' => $process->isSuccessful(),
            'output' => $process->getOutput(),
            'error' => trim($process->getErrorOutput() ?: $process->getOutput()),
        ];
    }

    protected function commonWindowsTesseractPaths(): array
    {
        return array_values(array_filter([
            getenv('ProgramFiles') ? getenv('ProgramFiles') . '\Tesseract-OCR\tesseract.exe' : null,
            getenv('ProgramW6432') ? getenv('ProgramW6432') . '\Tesseract-OCR\tesseract.exe' : null,
            getenv('ProgramFiles(x86)') ? getenv('ProgramFiles(x86)') . '\Tesseract-OCR\tesseract.exe' : null,
            getenv('LOCALAPPDATA') ? getenv('LOCALAPPDATA') . '\Programs\Tesseract-OCR\tesseract.exe' : null,
        ]));
    }

    protected function isMissingMalayLanguagePack(string $error): bool
    {
        $error = strtolower($error);

        return str_contains($error, 'msa.traineddata')
            || (str_contains($error, 'failed loading language') && str_contains($error, 'msa'));
    }

    protected function cleanOcrError(string $error): string
    {
        $error = trim(preg_replace('/\s+/', ' ', $error));

        return $error !== '' ? $error : 'Tesseract returned no text.';
    }

    protected function extractItems($ocrText): array
    {
        $prompt = "Extract items with prices from this receipt text. Return JSON array with fields: description, amount, category.
                   Categories: food, transport, shopping, entertainment, education, bills, health, other.
                   Receipt: \"\"\"{$ocrText}\"\"\"
                   Only JSON. Example: [{\"description\":\"Nasi Lemak\",\"amount\":5.50,\"category\":\"food\"}]";

        $ai = new AICategorizer();
        $response = $ai->callGemini($prompt);
        $items = $this->decodeItems($response);

        return count($items) > 0 ? $items : $this->extractItemsLocally($ocrText);
    }

    protected function decodeItems($response): array
    {
        $json = preg_replace('/```json|```/', '', (string) $response);
        $items = json_decode($json, true);

        if (! is_array($items)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($item) {
            if (! is_array($item) || ! isset($item['amount'])) {
                return null;
            }

            return [
                'description' => trim((string) ($item['description'] ?? 'Unknown item')),
                'amount' => (float) $item['amount'],
                'category' => trim((string) ($item['category'] ?? 'other')),
            ];
        }, $items)));
    }

    protected function extractItemsLocally($ocrText): array
    {
        $ai = new AICategorizer();
        $items = [];

        foreach ($this->receiptLines($ocrText) as $line) {
            $item = $this->parseReceiptLine($line, $ai);

            if (! $item) {
                continue;
            }

            $items[] = $item;

            if (count($items) >= 20) {
                break;
            }
        }

        return $items;
    }

    protected function receiptLines($ocrText): array
    {
        $text = preg_replace('/\s*,\s*/', "\n", (string) $ocrText);
        $lines = preg_split('/\R+/', $text) ?: [];

        return array_values(array_filter(array_map(function ($line) {
            return trim(preg_replace('/\s+/', ' ', $line));
        }, $lines)));
    }

    protected function parseReceiptLine($line, AICategorizer $ai): ?array
    {
        $line = trim($line);

        if ($line === '' || $this->isReceiptSummaryLine($line) || $this->isReceiptMetadataLine($line)) {
            return null;
        }

        if (! preg_match_all('/(?:RM\s*)?\d+(?:[.,]\d{2})\b/i', $line, $amountMatches)) {
            return null;
        }

        $amountText = end($amountMatches[0]);
        $amount = (float) str_replace([',', 'RM', 'rm', ' '], ['.', '', '', ''], $amountText);

        if ($amount <= 0) {
            return null;
        }

        $description = $this->descriptionFromLine($line, $amountText);

        if ($description === '' || $this->isReceiptSummaryLine($description) || $this->isReceiptMetadataLine($description)) {
            return null;
        }

        return [
            'description' => $description,
            'amount' => $amount,
            'category' => $ai->categorize($description, $amount),
        ];
    }

    protected function descriptionFromLine($line, $amountText): string
    {
        $description = preg_replace('/\s+' . preg_quote($amountText, '/') . '\s*$/', '', $line);
        $description = preg_replace('/^\s*\d+\s+/', '', $description);
        $description = preg_replace('/@\s*\d+(?:[.,]\d{2})\b/i', '', $description);
        $description = preg_replace('/\bRM\s*\d+(?:[.,]\d{2})\b/i', '', $description);
        $description = preg_replace('/\s+\d+(?:[.,]\d{2})\b\s*$/', '', $description);
        $description = trim(preg_replace('/\s+/', ' ', $description));

        return trim($description, " \t\n\r\0\x0B:-,*");
    }

    protected function isReceiptSummaryLine($description): bool
    {
        $description = strtolower($description);

        foreach (['total', 'subtotal', 'net total', 'tax', 'sst', 'cash', 'change', 'balance', 'mydebit', 'visa', 'mastercard', 'rounding'] as $summaryTerm) {
            if (str_contains($description, $summaryTerm)) {
                return true;
            }
        }

        return false;
    }

    protected function isReceiptMetadataLine($description): bool
    {
        $description = strtolower($description);

        if (preg_match('/\d{1,2}\/\d{1,2}\/\d{2,4}/', $description)) {
            return true;
        }

        foreach (['invoice', 'order no', 'date', 'cashier', 'cover', 'prn', 'qty item', 'your number'] as $metadataTerm) {
            if (str_contains($description, $metadataTerm)) {
                return true;
            }
        }

        return false;
    }
}
