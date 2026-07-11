<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SavingsGoalReached extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $goalName,
        private readonly float $targetAmount
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'success',
            'title' => 'Target Tabungan Tercapai',
            'message' => "Selamat! Target tabungan \"{$this->goalName}\" sudah tercapai.",
            'goal_name' => $this->goalName,
            'target_amount' => round($this->targetAmount, 2),
            'action_url' => route('savings-goals.index'),
        ];
    }
}
