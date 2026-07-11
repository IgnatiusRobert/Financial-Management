<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiTipsService
{
    public function generateTipsResult(
        array $financialContext,
        array $previousTips = [],
        bool $preferVariation = false
    ): array
    {
        $apiKey = (string) config('services.groq.api_key');
        $model = (string) config('services.groq.model', 'llama-3.1-8b-instant');

        if ($apiKey === '') {
            return [
                'tips' => [],
                'ok' => false,
                'error' => 'GROQ_API_KEY is not configured.',
            ];
        }

        $prompt = $this->buildPrompt($financialContext, $previousTips, $preferVariation);

        try {
            $response = Http::withToken($apiKey)
                ->timeout(20)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $model,
                    'temperature' => $preferVariation ? 0.85 : 0.55,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a concise personal finance assistant.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if (! $response->successful()) {
                $apiError = (string) data_get($response->json(), 'error.message', 'Unknown Groq API error');
                Log::warning('AI tips request failed.', ['status' => $response->status(), 'error' => $apiError]);

                return [
                    'tips' => [],
                    'ok' => false,
                    'error' => "HTTP {$response->status()}: {$apiError}",
                ];
            }

            $text = (string) data_get($response->json(), 'choices.0.message.content', '');
            $tips = $this->parseTips($text);

            if (! empty($tips)) {
                return [
                    'tips' => $tips,
                    'ok' => true,
                    'error' => null,
                ];
            }

            return [
                'tips' => [],
                'ok' => false,
                'error' => 'Groq returned an invalid tips format.',
            ];
        } catch (\Throwable $exception) {
            Log::warning('AI tips request exception.', ['message' => $exception->getMessage()]);

            return [
                'tips' => [],
                'ok' => false,
                'error' => $exception->getMessage(),
            ];
        }
    }

    public function fallbackTips(array $financialContext): array
    {
        $tips = [];
        $expenseRatio = (int) ($financialContext['expense_ratio_percent'] ?? 0);
        $goalProgress = (int) ($financialContext['average_goal_progress_percent'] ?? 0);
        $budgetUtilization = (int) ($financialContext['budget_utilization_percent'] ?? 0);
        $topCategory = (string) ($financialContext['top_expense_category'] ?? 'pengeluaran utama');
        $netCashflow = (float) ($financialContext['net_cashflow'] ?? 0);
        $budgetAmount = (float) ($financialContext['total_budget'] ?? 0);
        $goalsCount = (int) ($financialContext['goals_count'] ?? 0);

        $tips[] = [
            'title' => 'Ringkasan Finansial',
            'text' => "Arus kas bulan ini " . ($netCashflow >= 0 ? 'positif' : 'negatif') . ", rasio pengeluaran {$expenseRatio}%, progres rata-rata {$goalProgress}% dari {$goalsCount} target tabungan.",
        ];

        if ($expenseRatio >= 80) {
            $tips[] = [
                'title' => 'High Expense Ratio',
                'text' => "Pengeluaran bulan ini {$expenseRatio}% dari pemasukan. Coba kurangi kategori {$topCategory} untuk jaga ruang tabungan.",
            ];
        }

        if ($goalProgress < 60) {
            $tips[] = [
                'title' => 'Savings Goal Pace',
                'text' => "Rata-rata progres target tabungan masih {$goalProgress}%. Tambahkan transfer mingguan otomatis agar target lebih cepat tercapai.",
            ];
        }

        if ($budgetAmount > 0) {
            $tips[] = [
                'title' => 'Kontrol Budget',
                'text' => "Pemakaian budget berada di {$budgetUtilization}%. Tetapkan batas mingguan agar realisasi tidak melebihi pagu bulanan.",
            ];
        }

        if ($netCashflow <= 0) {
            $tips[] = [
                'title' => 'Cashflow Alert',
                'text' => 'Arus kas bulan ini negatif. Prioritaskan kebutuhan penting dan tunda belanja non-prioritas sampai cashflow kembali positif.',
            ];
        } else {
            $tips[] = [
                'title' => 'Positive Cashflow',
                'text' => 'Arus kas bulan ini positif. Alokasikan sebagian surplus ke dana darurat atau target tabungan prioritas.',
            ];
        }

        return collect($tips)->take(3)->values()->all();
    }

    private function buildPrompt(array $financialContext, array $previousTips, bool $preferVariation): string
    {
        $contextJson = json_encode($financialContext, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $previousTipsJson = json_encode($previousTips, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $variationRule = $preferVariation
            ? "- Rephrase wording versus previous tips while preserving the same key meaning and direction."
            : "- Keep phrasing stable and easy to understand.";

        return <<<PROMPT
Generate exactly 3 detailed AI allocation tips for a finance dashboard card.

Rules:
- Bahasa Indonesia.
- Practical and actionable with clear reason + action.
- Mention relevant numbers from context when possible.
- Max 48 words per tip.
- Include exactly:
  1) One overall abstract summary tip.
  2) One savings-goal/budget planning tip.
  3) One transaction/spending optimization tip.
- Jika menyebut nama (goal/kategori), gunakan hanya nama dari `user_input_names` yang berasal dari input pengguna.
- Jangan pernah menyebut istilah teknis seperti nama tabel, field, kolom, atau kata "database".
{$variationRule}
- Return strict JSON only:
{"tips":[{"title":"...","text":"..."},{"title":"...","text":"..."},{"title":"...","text":"..."}]}

Financial context:
{$contextJson}

Previous tips:
{$previousTipsJson}
PROMPT;
    }

    private function parseTips(string $rawText): array
    {
        $cleaned = trim($rawText);
        $decoded = json_decode($cleaned, true);

        if (! is_array($decoded) || ! is_array($decoded['tips'] ?? null)) {
            return [];
        }

        return collect($decoded['tips'])
            ->map(function ($tip) {
                if (! is_array($tip)) {
                    return null;
                }

                $title = trim((string) ($tip['title'] ?? ''));
                $text = trim((string) ($tip['text'] ?? ''));

                if ($title === '' || $text === '') {
                    return null;
                }

                return [
                    'title' => $title,
                    'text' => $text,
                ];
            })
            ->filter()
            ->take(3)
            ->values()
            ->all();
    }
}
