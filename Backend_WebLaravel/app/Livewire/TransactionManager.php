<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class TransactionManager extends Component
{
    public $description, $amount, $date, $type = "expense", $category_id;

    public function mount()
    {
        $this->date = date('Y-m-d');
        // Set default category based on initial type if available
        $defaultCategory = Category::where('type', $this->type)->first();
        if ($defaultCategory) {
            $this->category_id = $defaultCategory->id;
        }
    }

    public function updatedType()
    {
        // When type changes, reset category_id to the first available category for that type
        $defaultCategory = Category::where('type', $this->type)->first();
        $this->category_id = $defaultCategory ? $defaultCategory->id : null;
    }

    public function save()
    {
        Transaction::create([
            'amount' => $this->amount,
            'type' => $this->type,
            'category_id' => $this->category_id,
            'description' => $this->description,
            'date' => $this->date,
            'user_id' => auth()->id(),
        ]);

        $this->reset(["description", "amount"]);
    }

    public function delete($id)
    {
        Transaction::where("user_id", Auth::id())->where("id", $id)->delete();
    }

    public function render()
    {
        return view("livewire.transaction-manager", [
            "transactions" => Transaction::where("user_id", Auth::id())->with('category')->latest()->get(),
            "categories" => Category::where('type', $this->type)->get()
        ]);
    }
}
