<?php

namespace App\Filament\Widgets;

use App\Models\Lawsuit;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class MonthlyCaseCount extends ChartWidget
{
    protected static ?string $heading = 'মাসভিত্তিক মামলার সংখ্যা';
    protected static bool $isLazy = false;
    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        return auth()->user()?->can('widget_MonthlyCaseCount');
    }

    protected function getData(): array
    {
        $data = Trend::model(Lawsuit::class)
        ->between(
            start: now()->subYear(),
            end: now(),
        )
        ->perMonth()
        ->count();

        return [
            'datasets' => [
                [
                    'label' => 'মামলা',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22,163,74,.15)',
                    'tension' => 0.4,
                    'fill' => true,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 5,
                    'pointBackgroundColor' => '#e0dcf1',
                    'pointBorderColor' => '#16a34a',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 3,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
