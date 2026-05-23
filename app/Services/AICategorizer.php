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
                   Reply with ONE short, warm sentence in clear English. Be encouraging but realistic.
                   Reply only the sentence, no quotes.";

        return $this->callGemini($prompt, $this->localAffordMessage($itemName, $price, $remaining));
    }

    public function getTransactionMessage($description, $amount, $category)
    {
        $prompt = "A Malaysian student just logged this expense: $description, RM$amount, category $category.
                   Reply with ONE short friendly sentence in clear English. No quotes.";

        return $this->callGemini($prompt, $this->localTransactionMessage($amount, $category));
    }

    public function getSpendingInsight($weeklyData, $monthlyBudget)
    {
        $prompt = "Based on this student's weekly spending: " . json_encode($weeklyData) .
                  ". Monthly budget: RM$monthlyBudget.
                  Give ONE short insight (max 15 words) in clear, friendly English. Be helpful and practical.";

        return $this->callGemini($prompt, $this->localSpendingInsight($weeklyData, $monthlyBudget));
    }

    public function getPtptnHourlyNote(array $metrics, int|string|null $seed = null)
    {
        $fallback = $this->localPtptnHourlyNote($metrics, $seed);
        $prompt = 'Write ONE short PTPTN Mode awareness note for a Malaysian student.
                   Use these already-calculated numbers only:
                   Days left this month: ' . data_get($metrics, 'days_left') . '
                   Safe daily spend: RM' . data_get($metrics, 'daily_safe_spend') . '
                   PTPTN remaining: RM' . data_get($metrics, 'ptptn_remaining') . '
                   Status: ' . data_get($metrics, 'status') . '
                   Keep it clear, caring, and concise. Max 16 words.
                   Do not use markdown or quotes.';

        return $this->callGemini($prompt, $fallback);
    }

    public function getPtptnDailyNote(array $metrics, int|string|null $seed = null)
    {
        return $this->getPtptnHourlyNote($metrics, $seed);
    }

    public function getPtptnDashboardIntro(array $metrics, int|string|null $seed = null)
    {
        $fallback = $this->localPtptnDashboardIntro($metrics, $seed);
        $prompt = 'Write ONE short dashboard intro for active PTPTN Mode.
                   Use these already-calculated numbers only:
                   Days left this month: ' . data_get($metrics, 'days_left') . '
                   Safe daily spend: RM' . data_get($metrics, 'daily_safe_spend') . '
                   PTPTN remaining: RM' . data_get($metrics, 'ptptn_remaining') . '
                   Status: ' . data_get($metrics, 'status') . '
                   Keep it warm, student-friendly, and clear. Max 18 words.
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
                'You can afford this, but keep a small buffer for meals and transport.',
                'This looks safe today. Keep enough room for the rest of the month.',
                "Green light, but stay mindful of tomorrow's expenses.",
                'Approved for now. Make sure it still feels worth it after a second thought.',
                'Your balance can handle it, but avoid turning this into a daily habit.',
                'This purchase fits your budget. Keep tracking so it stays that way.',
            ];

            return $this->hourlyRotatingNote($notes, 'afford:' . $itemName . ':' . $price);
        }

        $notes = [
            'It is better to wait; this would put too much pressure on your budget.',
            'Pause for now and let your budget recover before buying this.',
            'Not today. Save gradually and make this easier on your future self.',
            'Your budget says no for now. Try again after trimming other spending.',
            'This purchase would make the month harder than it needs to be.',
            "Hold off for now. Your remaining RM{$remaining} needs stronger protection.",
        ];

        return $this->hourlyRotatingNote($notes, 'wait:' . $itemName . ':' . $price . ':' . $remaining);
    }

    protected function localTransactionMessage($amount, $category): string
    {
        if ($amount >= 1000) {
            $notes = [
                "RM{$amount} logged under {$category}. This is a large entry, so double-check the amount.",
                "Large expense alert: RM{$amount}. Confirm it is correct before it affects your budget.",
                "That RM{$amount} entry is large. Good tracking, but review it once more.",
            ];

            return $this->hourlyRotatingNote($notes, 'large:' . $category . ':' . $amount);
        }

        return $this->localCategoryComment($category, (float) $amount);
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

    protected function localPtptnHourlyNote(array $metrics, int|string|null $seed = null): string
    {
        $status = (string) data_get($metrics, 'status', 'on_track');
        $notes = match ($status) {
            'over_budget' => [
                'Pause non-essential spending this hour and let your balance recover.',
                'Use this hour for damage control: track first, spend later.',
                'Take a spending break now so the rest of the month stays manageable.',
            ],
            'protect_reserve' => [
                'Protect your reserve first; small treats can wait a little longer.',
                'Your reserve is tight, so keep spending simple this hour.',
                'Hold the line now and keep your PTPTN buffer breathing.',
            ],
            'using_ptptn' => [
                'PTPTN is helping now, so pace this hour carefully.',
                'Spend slowly this hour; your PTPTN runway still matters.',
                'Keep it steady. One careful hour protects the month.',
            ],
            'tight' => [
                'Choose only small essentials this hour; your budget needs gentle handling.',
                'Go easy for now. Even small savings help your runway.',
                'Low runway alert: choose needs first and wants later.',
            ],
            default => [
                'Your runway looks okay this hour; keep spending calm and intentional.',
                'You are on track. Keep each purchase thoughtful.',
                'Good pace so far. Protect the month one choice at a time.',
            ],
        };

        return $this->hourlyRotatingNote($notes, 'ptptn-note:' . $status . ':' . $seed);
    }

    protected function localPtptnDashboardIntro(array $metrics, int|string|null $seed = null): string
    {
        $status = (string) data_get($metrics, 'status', 'on_track');
        $daysLeft = (int) data_get($metrics, 'days_left', 1);
        $dailySafeSpend = number_format((float) data_get($metrics, 'daily_safe_spend', 0), 2);

        $notes = match ($status) {
            'over_budget' => [
                'PTPTN Mode is in emergency mode today; pause wants and protect the month.',
                'PTPTN runway is under pressure. Track each expense and slow spending.',
                'Budget alert is on. PTPTN Mode recommends essentials only for now.',
            ],
            'protect_reserve' => [
                'PTPTN reserve needs protection today; keep spending careful and simple.',
                'Your reserve is close, so PTPTN Mode is watching smaller expenses.',
                'Careful today: protect the PTPTN buffer before saying yes to wants.',
            ],
            'using_ptptn' => [
                "PTPTN is covering the gap now; {$daysLeft} days left, spend steadily.",
                "PTPTN runway active: RM{$dailySafeSpend} safe daily spend, avoid surprise splurges.",
                'PTPTN loan mode is helping today, so keep wants small and choices intentional.',
            ],
            'tight' => [
                "PTPTN Mode says budget is tight; RM{$dailySafeSpend} daily means careful spending only.",
                'PTPTN small-spend day. Keep the runway alive until month end.',
                'Your PTPTN month can still survive, but every small purchase matters now.',
            ],
            default => [
                "PTPTN Mode is on duty: {$daysLeft} days left and your runway still looks okay.",
                'PTPTN Mode is tracking your safe spend and reserve today.',
                'PTPTN Mode is quietly guarding your runway, reserve, and daily spending choices.',
            ],
        };

        return $this->hourlyRotatingNote($notes, 'ptptn-intro:' . $status . ':' . $seed);
    }

    protected function localCategoryComment(string $category, float $amount): string
    {
        return match (strtolower($category)) {
            'food', 'beverages' => $this->foodComment($amount),
            'shopping', 'personal care', 'technology' => $this->shoppingComment($amount),
            'transport', 'transportation expenses' => $this->transportComment($amount),
            'entertainment' => $this->entertainmentComment($amount),
            'bills', 'living expenses', 'family care', 'debt payments' => $this->billsComment(),
            'education' => $this->educationComment(),
            'health', 'health care' => $this->healthComment(),
            'savings and investments' => 'Good move. This supports your future buffer.',
            default => 'Noted. Tracking this helps you understand where your money goes.',
        };
    }

    private function foodComment(float $amount): string
    {
        if ($amount > 25) {
            $notes = [
                "RM{$amount} for food is a bigger meal spend. Balance it with simpler meals later.",
                'That food spend is on the higher side. Keep the next few meals modest.',
                'A pricier meal is okay sometimes, but make sure it fits the week.',
            ];
        } else {
            $notes = [
                'Reasonable food spending. Keep this steady.',
                'Good food entry. This looks manageable for a student budget.',
                'Affordable and practical. Nice tracking.',
            ];
        }

        return $this->hourlyRotatingNote($notes, 'food:' . $amount);
    }

    private function shoppingComment(float $amount): string
    {
        $notes = [
            'Shopping logged. Check whether this was a need or a want.',
            'This purchase is tracked. Keep an eye on repeated small shopping costs.',
            'Shopping can add up quickly, so balance this with quieter spending later.',
            'Good tracking. A quick second look can help prevent impulse spending.',
        ];

        return $this->hourlyRotatingNote($notes, 'shopping:' . $amount);
    }

    private function transportComment(float $amount): string
    {
        $notes = [
            'Transport logged. Watch repeated rides because they add up quietly.',
            'Getting around costs money too. Keep this category visible.',
            'Transport spending tracked. A planned route can save a little later.',
        ];

        return $this->hourlyRotatingNote($notes, 'transport:' . $amount);
    }

    private function entertainmentComment(float $amount): string
    {
        $notes = [
            'A bit of fun is okay when it stays inside the budget.',
            'Entertainment logged. Keep it balanced with your essentials.',
            'Enjoyment matters too, but keep the spending limit clear.',
        ];

        return $this->hourlyRotatingNote($notes, 'entertainment:' . $amount);
    }

    private function billsComment(): string
    {
        $notes = [
            'Bills tracked. Staying on top of them keeps the month calmer.',
            'Necessary expense logged. Good job keeping this visible.',
            'This one is important. Tracking it helps avoid surprises.',
        ];

        return $this->hourlyRotatingNote($notes, 'bills');
    }

    private function educationComment(): string
    {
        $notes = [
            'Education spending is an investment, but it still deserves tracking.',
            'Study-related cost logged. Keep learning expenses planned.',
            'This supports your studies. Keep it inside the monthly plan.',
        ];

        return $this->hourlyRotatingNote($notes, 'education');
    }

    private function healthComment(): string
    {
        $notes = [
            'Health comes first. This is a valid expense to track.',
            'Taking care of yourself is smart spending.',
            'Health spending logged. Keep this priority protected.',
        ];

        return $this->hourlyRotatingNote($notes, 'health');
    }

    protected function hourlyRotatingNote(array $notes, int|string|null $seed = null): string
    {
        $hourIndex = intdiv(Carbon::now()->getTimestamp(), 3600);
        $seedIndex = abs((int) crc32((string) $seed));

        return $notes[($hourIndex + $seedIndex) % count($notes)];
    }
}
