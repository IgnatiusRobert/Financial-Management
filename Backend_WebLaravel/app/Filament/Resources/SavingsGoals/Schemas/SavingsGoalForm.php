<?php

namespace App\Filament\Resources\SavingsGoals\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SavingsGoalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('target_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('current_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('icon'),
                TextInput::make('color'),
                DatePicker::make('target_date'),
            ]);
    }
}
