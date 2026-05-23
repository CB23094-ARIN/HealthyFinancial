<?php

namespace App\Services;

use Carbon\Carbon;
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

    public function getPtptnDailyNote(array $metrics, int|string|null $seed = null)
    {
        $fallback = $this->localPtptnDailyNote($metrics, $seed);
        $prompt = 'Write ONE short PTPTN Mode awareness note for a Malaysian student.
                   Use these already-calculated numbers only:
                   Days left this month: ' . data_get($metrics, 'days_left') . '
                   Safe daily spend: RM' . data_get($metrics, 'daily_safe_spend') . '
                   PTPTN remaining: RM' . data_get($metrics, 'ptptn_remaining') . '
                   Status: ' . data_get($metrics, 'status') . '
                   Keep it cute, caring, and concise. Max 16 words.
                   Do not use markdown or quotes.';

        return $this->callGemini($prompt, $fallback);
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
            $notes = [
                'Can la... can la, but keep a small buffer for other stuffs too ><.',
                'Looks okay today; just do not let one want disturb the month.',
                'Green light, but leave some breathing room for tomorrow pleaseeee.',
            ];

            return $this->dailyRotatingNote($notes, 'afford:' . $itemName . ':' . $price);
        }

        $notes = [
            'Better wait a bit; this would push the budget too hard.',
            'Alamak, pause first and let the budget recover before buying.',
            'Not today lah; save slowly and make future you less stressed.',
        ];

        return $this->dailyRotatingNote($notes, 'wait:' . $itemName . ':' . $price . ':' . $remaining);
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

    protected function localPtptnDailyNote(array $metrics, int|string|null $seed = null): string
    {
        $status = (string) data_get($metrics, 'status', 'on_track');
        $notes = match ($status) {
            'over_budget' => [
                'Alamak, pause non-essentials today and let your balance recover.',
                'Today is damage-control day; track first, spend later lah.',
                'Big spending break now, future you will thank you.',
            ],
            'protect_reserve' => [
                'Protect that reserve first; little treats can wait a bit.',
                'Reserve zone is tight, so keep today simple lah.',
                'Hold the line today and keep PTPTN breathing.',
            ],
            'using_ptptn' => [
                'PTPTN is helping now, so pace today like a pro.',
                'Spend slow today; your PTPTN runway still matters.',
                'Keep it steady lah, one careful day protects the month.',
            ],
            'tight' => [
                'Tiny spends only today; your budget needs soft handling.',
                'Go gentle today, even small savings count lah.',
                'Low runway alert; choose needs first, wants later.',
            ],
            default => [
                'Nice runway today; keep the calm spending streak going.',
                'You are on track, so keep spending chill and intentional.',
                'Good pace lah, protect the month one choice at a time.',
            ],
        };

        $dayIndex = (int) Carbon::now()->format('z');
        $seedIndex = abs((int) crc32($status . ':' . (string) $seed));

        return $notes[($dayIndex + $seedIndex) % count($notes)];
    }

    protected function dailyRotatingNote(array $notes, int|string|null $seed = null): string
    {
        $dayIndex = (int) Carbon::now()->format('z');
        $seedIndex = abs((int) crc32((string) $seed));

        return $notes[($dayIndex + $seedIndex) % count($notes)];
    }
}
