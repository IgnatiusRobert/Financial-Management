<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SavingsGoalDeadlineReminder extends Notification
{
    use Queueable;

    public function __construct(
        private readonly int $goalId,
        private readonly string $goalName,
        private readonly string $targetDate,
        private readonly float $targetAmount,
        private readonly float $currentAmount
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'warning',
            'title' => 'Target Tabungan Mendekati Tenggat',
            'message' => "Sisa 7 hari untuk target \"{$this->goalName}\". Ayo tambah tabungan agar target tercapai.",
            'goal_id' => $this->goalId,
            'goal_name' => $this->goalName,
            'target_date' => $this->targetDate,
            'target_amount' => round($this->targetAmount, 2),
            'current_amount' => round($this->currentAmount, 2),
            'reminder_key' => 'deadline_in_7_days',
            'action_url' => route('savings-goals.index'),
        ];
    }
}
