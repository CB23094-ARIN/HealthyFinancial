<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class AICategorizer
{
    protected $apiKey;
    protected $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    public function categorize($description, $amount = null)
    {
        if (! $this->hasUsableApiKey()) {
            return $this->guessCategory($description);
        }

        $prompt = "You are HealthyFinancial, a Malaysian student finance assistant.
                   Categorize this expense into ONE of:
                   ['food', 'transport', 'shopping', 'entertainment', 'education', 'bills', 'health', 'other'].

                   Malaysian terms:
                   - 'mamak', 'teh tarik', 'nasi lemak', 'kopi', 'GrabFood', 'FoodPanda' -> food
                   - 'topup', 'TnG', 'parking', 'petrol', 'grabcar' -> transport
                   - 'Shopee', 'Lazada', 'TikTok shop', 'unifi' -> shopping or bills
                   - 'Netflix', 'cinema', 'game', 'Steam' -> entertainment
                   - 'PTPTN', 'yuran', 'buku' -> education

                   Description: \"{$description}\"
                   Amount: RM" . ($amount ?? 'unknown') . "

                   Reply ONLY with the category name (one word).";

        $response = $this->callGemini($prompt);
        $allowed = ['food', 'transport', 'shopping', 'entertainment', 'education', 'bills', 'health', 'other'];
        $category = trim(strtolower($response));

        return in_array($category, $allowed) ? $category : $this->guessCategory($description);
    }

    public function getFunMessage($itemName, $price, $remaining)
    {
        $prompt = "Student asks: 'Can I afford $itemName for RM$price?' They have RM$remaining left this month.
                   Reply with ONE very short, cute, funny sentence in Manglish or Malay. Be encouraging but realistic.
                   Reply only the sentence, no quotes.";

        return $this->callGemini($prompt, $this->localAffordMessage($itemName, $price, $remaining));
    }

    public function getTransactionMessage($description, $amount, $category)
    {
        $prompt = "A Malaysian student just logged this expense: $description, RM$amount, category $category.
                   Reply with ONE short friendly sentence. No quotes.";

        return $this->callGemini($prompt, $this->localTransactionMessage($amount, $category));
    }

    public function getSpendingInsight($weeklyData, $monthlyBudget)
    {
        $prompt = "Based on this student's weekly spending: " . json_encode($weeklyData) .
                  ". Monthly budget: RM$monthlyBudget.
                  Give ONE short insight (max 15 words) in Manglish. Be helpful but playful.";

        return $this->callGemini($prompt, $this->localSpendingInsight($weeklyData, $monthlyBudget));
    }

    public function callGemini($prompt, $fallback = '')
    {
        if (! $this->hasUsableApiKey()) {
            return $fallback;
        }

        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->withOptions(['verify' => false])
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->apiUrl . '?key=' . $this->apiKey, [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 100],
                ]);

            if ($response->failed()) {
                return $fallback;
            }

            $text = $response->json('candidates.0.content.parts.0.text');

            return trim((string) $text) ?: $fallback;
        } catch (Throwable) {
            return $fallback;
        }
    }

    protected function hasUsableApiKey(): bool
    {
        $key = trim((string) $this->apiKey);

        return $key !== '' && $key !== 'your_google_gemini_api_key_here';
    }

    protected function guessCategory($description): string
    {
        $text = strtolower($description);

        $keywords = [
            'food' => ['mamak', 'teh', 'tea', 'nasi', 'kopi', 'coffee', 'grabfood', 'foodpanda', 'lunch', 'dinner', 'breakfast', 'cafe', 'restaurant', 'kfc', 'mcd', 'ayam', 'sambal', 'lemon', 'honeydew', 'set a', 'dine in'],
            'transport' => ['topup', 'tng', 'touch n go', 'parking', 'petrol', 'fuel', 'grabcar', 'grab', 'bus', 'train', 'mrt', 'lrt', 'toll'],
            'shopping' => ['shopee', 'lazada', 'tiktok shop', 'uniqlo', 'clothes', 'shirt', 'shoe'],
            'entertainment' => ['netflix', 'cinema', 'movie', 'game', 'steam', 'spotify'],
            'education' => ['ptptn', 'yuran', 'buku', 'book', 'tuition', 'course', 'print'],
            'bills' => ['unifi', 'maxis', 'celcom', 'digi', 'electric', 'water', 'bill', 'rent', 'phone', 'internet'],
            'health' => ['clinic', 'pharmacy', 'doctor', 'medicine', 'hospital', 'dentist', 'guardian', 'watsons'],
        ];

        foreach ($keywords as $category => $terms) {
            foreach ($terms as $term) {
                if (str_contains($text, $term)) {
                    return $category;
                }
            }
        }

        return 'other';
    }

    protected function localAffordMessage($itemName, $price, $remaining): string
    {
        if ($price <= $remaining) {
            return 'Looks affordable, but keep some buffer for meals and transport.';
        }

        return 'Better wait a bit; this would push the budget too hard.';
    }

    protected function localTransactionMessage($amount, $category): string
    {
        if ($amount >= 1000) {
            return "Large expense logged under {$category}; double-check the amount if needed.";
        }

        if ($category === 'other') {
            return 'Logged. A clearer description next time can improve the category.';
        }

        return "Logged under {$category}. Small tracking wins add up.";
    }

    protected function localSpendingInsight($weeklyData, $monthlyBudget): string
    {
        $totals = collect($weeklyData)->mapWithKeys(function ($row) {
            $category = data_get($row, 'category', 'other');
            $total = (float) data_get($row, 'total', 0);

            return [$category => $total];
        });

        $weeklyTotal = $totals->sum();

        if ($weeklyTotal <= 0) {
            return "Start with today's expenses; the pattern will show up soon.";
        }

        if ($monthlyBudget <= 0) {
            return 'Set your monthly budget so your health score has context.';
        }

        if ($weeklyTotal > $monthlyBudget) {
            return 'Spending is already past budget; review big entries first.';
        }

        $topCategory = $totals->sortDesc()->keys()->first();

        if ($topCategory && $topCategory !== 'other') {
            return "Most spending is on {$topCategory}; check one easy trim today.";
        }

        return 'Keep tracking daily; the clearest saving opportunity will appear soon.';
    }
}
