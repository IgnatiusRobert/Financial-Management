<?php

namespace App\Console\Commands;

use App\Models\SavingsGoal;
use App\Models\User;
use App\Notifications\SavingsGoalDeadlineReminder;
use Illuminate\Support\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('goals:send-deadline-reminders')]
#[Description('Send savings goal reminders for deadlines in 7 days')]
class SendSavingsGoalDeadlineReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetDate = Carbon::today()->addDays(7)->toDateString();

        $goals = SavingsGoal::query()
            ->whereDate('target_date', $targetDate)
            ->whereColumn('current_amount', '<', 'target_amount')
            ->get();

        $sent = 0;

        foreach ($goals as $goal) {
            $user = User::find($goal->user_id);
            if (! $user) {
                continue;
            }

            $alreadySent = $user->notifications()
                ->where('type', SavingsGoalDeadlineReminder::class)
                ->whereDate('created_at', Carbon::today()->toDateString())
                ->where('data', 'like', '%"goal_id":' . $goal->id . '%')
                ->where('data', 'like', '%"reminder_key":"deadline_in_7_days"%')
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $user->notify(new SavingsGoalDeadlineReminder(
                goalId: (int) $goal->id,
                goalName: (string) $goal->name,
                targetDate: (string) $goal->target_date,
                targetAmount: (float) $goal->target_amount,
                currentAmount: (float) $goal->current_amount
            ));

            $sent++;
        }

        $this->info("Deadline reminder sent: {$sent} notification(s).");

        return self::SUCCESS;
    }
}
