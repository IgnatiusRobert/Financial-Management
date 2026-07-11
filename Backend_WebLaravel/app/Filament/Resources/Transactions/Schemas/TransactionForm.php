<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('category_id')
                    ->numeric(),
                DatePicker::make('date')
                    ->required(),
                TextInput::make('description')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('Completed'),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('type')
                    ->required()
                    ->default('expense'),
            ]);
    }
}
