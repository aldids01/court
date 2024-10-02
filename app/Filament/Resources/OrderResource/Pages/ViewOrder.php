<?php

namespace App\Filament\Resources\OrderResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\OrderResource;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('detail')
                ->label('Detail')
                ->color('info')
                ->url(fn ($record): string => route('details', $record)),
        ];
    }
}
