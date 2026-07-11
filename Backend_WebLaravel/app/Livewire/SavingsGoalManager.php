<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\SavingsGoal;
use App\Notifications\SavingsGoalReached;
use Illuminate\Support\Facades\Auth;

class SavingsGoalManager extends Component
{
    public $name, $target_amount, $current_amount = 0, $target_date;
    public ?int $editingGoalId = null;

    public function save()
    {
        $userId = Auth::id();

        if ($this->editingGoalId) {
            $goal = SavingsGoal::where("user_id", $userId)
                ->where("id", $this->editingGoalId)
                ->firstOrFail();

            $goal->update([
                "name" => $this->name,
                "target_amount" => $this->target_amount,
                "current_amount" => $this->current_amount ?: 0,
                "target_date" => $this->target_date ?: null,
            ]);

            session()->flash('message', 'Target tabungan berhasil diperbarui.');
        } else {
            $goal = SavingsGoal::create([
                "user_id" => $userId,
                "name" => $this->name,
                "target_amount" => $this->target_amount,
                "current_amount" => $this->current_amount ?: 0,
                "target_date" => $this->target_date ?: null
            ]);

            session()->flash('message', 'Target tabungan berhasil dibuat.');
        }

        $this->notifyIfGoalReached($goal);
        $this->resetForm();
    }

    public function delete($id)
    {
        SavingsGoal::where("user_id", Auth::id())->where("id", $id)->delete();

        if ($this->editingGoalId === (int) $id) {
            $this->resetForm();
        }
    }

    public function edit($id): void
    {
        $goal = SavingsGoal::where("user_id", Auth::id())->where("id", $id)->firstOrFail();

        $this->editingGoalId = (int) $goal->id;
        $this->name = $goal->name;
        $this->target_amount = $goal->target_amount;
        $this->current_amount = $goal->current_amount;
        $this->target_date = $goal->target_date;
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        return view("livewire.savings-goal-manager", [
            "goals" => SavingsGoal::where("user_id", Auth::id())->latest()->get()
        ]);
    }

    private function notifyIfGoalReached(SavingsGoal $goal): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        if ((float) $goal->target_amount <= 0 || (float) $goal->current_amount < (float) $goal->target_amount) {
            return;
        }

        $alreadyNotified = $user->unreadNotifications()
            ->where('type', SavingsGoalReached::class)
            ->whereDate('created_at', now()->toDateString())
            ->where('data', 'like', '%"goal_name":"' . $goal->name . '"%')
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        $user->notify(new SavingsGoalReached(
            goalName: (string) $goal->name,
            targetAmount: (float) $goal->target_amount
        ));
    }

    private function resetForm(): void
    {
        $this->editingGoalId = null;
        $this->reset(["name", "target_amount", "current_amount", "target_date"]);
        $this->current_amount = 0;
    }
}
