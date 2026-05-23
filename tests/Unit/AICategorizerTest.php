<?php

namespace Tests\Unit;

use App\Services\AICategorizer;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class AICategorizerTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_guesses_categories_without_api_key(): void
    {
        $ai = new AICategorizer();

        $this->assertSame('food', $ai->categorize('Nasi Lemak', 5.50));
        $this->assertSame('transport', $ai->categorize('TnG topup', 20));
    }

    public function test_it_uses_user_friendly_fallback_messages_without_api_key(): void
    {
        $ai = new AICategorizer();

        $insight = $ai->getSpendingInsight([
            ['category' => 'food', 'total' => 75],
        ], 500);

        $message = $ai->getTransactionMessage('asd', 100000, 'other');

        $this->assertStringNotContainsString('GEMINI_API_KEY', $insight);
        $this->assertStringNotContainsString('unavailable', $insight);
        $this->assertStringContainsString('large', strtolower($message));
    }

    public function test_ptptn_note_fallback_changes_by_hour(): void
    {
        $ai = new AICategorizer();
        $metrics = [
            'days_left' => 9,
            'daily_safe_spend' => 5.56,
            'ptptn_remaining' => 50,
            'status' => 'using_ptptn',
        ];

        Carbon::setTestNow('2026-05-23 10:00:00');
        $firstNote = $ai->getPtptnHourlyNote($metrics, 1);

        Carbon::setTestNow('2026-05-23 11:00:00');
        $secondNote = $ai->getPtptnHourlyNote($metrics, 1);

        $this->assertNotSame($firstNote, $secondNote);
        $this->assertStringNotContainsString('GEMINI_API_KEY', $firstNote);
        $this->assertNotManglish($firstNote);
    }

    public function test_can_afford_ai_note_fallback_changes_by_hour(): void
    {
        $ai = new AICategorizer();

        Carbon::setTestNow('2026-05-23 10:00:00');
        $firstNote = $ai->getFunMessage('food', 10, 50);

        Carbon::setTestNow('2026-05-23 11:00:00');
        $secondNote = $ai->getFunMessage('food', 10, 50);

        $this->assertNotSame($firstNote, $secondNote);
        $this->assertStringNotContainsString('GEMINI_API_KEY', $firstNote);
        $this->assertNotManglish($firstNote);
    }

    public function test_ptptn_dashboard_intro_fallback_changes_by_hour(): void
    {
        $ai = new AICategorizer();
        $metrics = [
            'days_left' => 9,
            'daily_safe_spend' => 5.56,
            'ptptn_remaining' => 50,
            'status' => 'using_ptptn',
        ];

        Carbon::setTestNow('2026-05-23 10:00:00');
        $firstIntro = $ai->getPtptnDashboardIntro($metrics, 1);

        Carbon::setTestNow('2026-05-23 11:00:00');
        $secondIntro = $ai->getPtptnDashboardIntro($metrics, 1);

        $this->assertNotSame($firstIntro, $secondIntro);
        $this->assertStringContainsString('PTPTN', $firstIntro);
        $this->assertStringNotContainsString('GEMINI_API_KEY', $firstIntro);
        $this->assertNotManglish($firstIntro);
    }

    public function test_transaction_message_uses_category_specific_fallback(): void
    {
        $ai = new AICategorizer();

        Carbon::setTestNow('2026-05-23 10:00:00');

        $foodMessage = $ai->getTransactionMessage('Nasi Lemak', 8, 'Food');
        $shoppingMessage = $ai->getTransactionMessage('Shopee case', 30, 'shopping');

        $this->assertNotSame($foodMessage, $shoppingMessage);
        $this->assertStringNotContainsString('GEMINI_API_KEY', $foodMessage);
        $this->assertStringNotContainsString('unavailable', $shoppingMessage);
        $this->assertNotManglish($foodMessage);
        $this->assertNotManglish($shoppingMessage);
    }

    private function assertNotManglish(string $message): void
    {
        $oldToneMarkers = [' lah', ' wei', ' hor', 'Alamak', 'mamak'];

        foreach ($oldToneMarkers as $marker) {
            $this->assertStringNotContainsString($marker, $message);
        }
    }
}
