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
            Actions\EditAction::make()
                ->visible(fn($record)=>$record->status != 'Approved'),
            Actions\Action::make('detail')
                ->label('View PDF')
                ->color('info')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible(fn($record)=>$record->status == 'Approved')
                ->url(fn($record)=>"/admin/orders/details/$record->id"),
        ];
    }
}
