<?php

namespace Tests\Unit;

use App\Services\AICategorizer;
use PHPUnit\Framework\TestCase;

class AICategorizerTest extends TestCase
{
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
        $this->assertStringContainsString('Large expense', $message);
    }
}
