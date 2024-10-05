<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('View PDF')
                ->color('info')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible(fn($record)=>$record->status == 'Approved')
                ->url(fn($record)=>"/admin/orders/details/$record->id"),
            Actions\DeleteAction::make()
                ->visible(fn($record)=>$record->status == 'Pending' && auth()->check() && auth()->user()->can('Delete Form')),
        ];
    }
}
