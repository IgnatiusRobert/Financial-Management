<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BudgetUsageAlert extends Notification
{
    use Queueable;

    public function __construct(
        private readonly float $utilizationPercent,
        private readonly float $spent,
        private readonly float $budget
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isOverBudget = $this->utilizationPercent >= 100;

        return [
            'type' => $isOverBudget ? 'error' : 'warning',
            'title' => $isOverBudget ? 'Budget Terlampaui' : 'Budget Hampir Habis',
            'message' => $isOverBudget
                ? 'Pengeluaran bulan ini sudah melebihi batas budget.'
                : 'Pengeluaran bulan ini sudah mencapai 80% dari budget.',
            'utilization_percent' => round($this->utilizationPercent),
            'spent' => round($this->spent, 2),
            'budget' => round($this->budget, 2),
            'action_url' => route('reports.index'),
        ];
    }
}
