<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\OrderItem;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;

class FormsChart extends ChartWidget
{
    protected static ?string $maxHeight = '400px';
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Forms Filling';
    protected function getData(): array
    {
        $data = Trend::model(OrderItem::class)
        ->between(
            start: now()->startOfMonth(),
            end: now()->endOfMonth(),
        )
        ->perDay()
        ->count();

    return [
        'datasets' => [
            [
                'label' => 'Daily Count',
                'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
            ],
        ],
       'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('M jS y')),
    ];
    }
    public function getDescription(): ?string
    {
        return 'The number of forms filled per month.';
    }
    protected function getType(): string
    {
        return 'line';
    }
    // protected function getOptions(): array
    // {
    //     return [
    //         'maintainAspectRatio' => false,
    //         'scales' => [
    //             'y' => [
    //                 'beginAtZero' => false,
    //             ],
    //         ],

    //         'layout' => [
    //             'padding' => [
    //                 'top' => 0,
    //                 'bottom' => 0,
    //             ],
    //         ],
    //     ];
    // }
}
