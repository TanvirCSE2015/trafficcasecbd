<?php

namespace App\Filament\Widgets;

use App\Models\CaseInvoice;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class MonthlyDismissedCase extends ChartWidget
{
    protected static ?string $heading = 'মাসভিত্তিক নিষ্পত্তিকৃত মামালার সংখ্যা';
    protected static bool $isLazy = false;
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()?->can('widget_MonthlyDismissedCase');
    }

    protected function getData(): array
    {
        $data = Trend::model(CaseInvoice::class)
        ->between(
            start: now()->subYear(),
            end: now(),
        )
        ->perMonth()
        ->count();

        return [
            'datasets' => [
                [
                    'label' => 'নিষ্পত্তিকৃত মামালা',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => [
                        '#fb923c', // orange
                        '#4ade80', // green
                        '#60a5fa', // blue
                        '#a78bfa', // purple

                        '#34d399', // teal
                        '#818cf8', // indigo
                        '#fbbf24', // amber
                        '#2dd4bf', // cyan
                        '#c084fc', // violet
                    ],
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
