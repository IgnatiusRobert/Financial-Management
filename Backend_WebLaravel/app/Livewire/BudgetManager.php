<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class BudgetManager extends Component
{
    public $amount, $period = "monthly", $category_id;

    public function save()
    {
        Budget::create([
            "user_id" => Auth::id(),
            "amount" => $this->amount,
            "period" => $this->period,
            "category_id" => $this->category_id
        ]);
        $this->reset(["amount", "category_id"]);
    }

    public function delete($id)
    {
        Budget::where("user_id", Auth::id())->where("id", $id)->delete();
    }

    public function render()
    {
        return view("livewire.budget-manager", [
            "budgets" => Budget::where("user_id", Auth::id())->with('category')->latest()->get(),
            "categories" => Category::all()
        ]);
    }
}
