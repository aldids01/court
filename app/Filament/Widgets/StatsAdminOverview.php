<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Template;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsAdminOverview extends BaseWidget
{
    protected static ?int $sort = 2;
    protected function getStats(): array
    {
        $orderCounts = Order::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(7)) // Last 7 days
            ->groupBy('date')
            ->pluck('count') // Get only the 'count' column
            ->toArray();
            $orderCounts = array_pad($orderCounts, -7, 0);

            $formCounts = Template::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('is_active', '=', 1)
            ->where('created_at', '>=', Carbon::now()->subDays(7)) // Last 7 days
            ->groupBy('date')
            ->pluck('count') // Get only the 'count' column
            ->toArray();
            $formCounts = array_pad($formCounts, -7, 0);

            $itemCounts = OrderItem::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(7)) // Last 7 days
            ->groupBy('date')
            ->pluck('count') // Get only the 'count' column
            ->toArray();
            $itemCounts = array_pad($itemCounts, -7, 0);

        return [
            Stat::make('Forms',Template::query()->where('is_active', '=', 1)->count())
            ->description('Form created')
            ->descriptionIcon('heroicon-o-document')
            ->chart($formCounts)
            ->color('primary'),
        Stat::make('Filled', OrderItem::query()->count())
            ->description('Total number of forms filled for clients')
            ->descriptionIcon('heroicon-o-document-text')
            ->chart($itemCounts)
            ->color('danger'),
        Stat::make('Clients', Order::query()->count())
            ->description('Clients attended to')
            ->descriptionIcon('heroicon-o-users')
            ->chart($orderCounts)
            ->color('info'),
        ];
    }
}
