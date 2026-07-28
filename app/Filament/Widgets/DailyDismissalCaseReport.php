<?php

namespace App\Filament\Widgets;

use App\Models\CaseInvoice;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class DailyDismissalCaseReport extends ChartWidget
{
   protected static ?string $heading = 'দৈনিক নিষ্পত্তিকৃত মামালার রিপোর্ট';
   protected static bool $isLazy = false;
   protected static ?int $sort = 2;
   protected int | string | array $columnSpan = 'full';
   

    public static function canView(): bool
    {
        return auth()->user()?->can('widget_DailyDismissalCaseReport');
    }

    protected function getData(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $data = Trend::model(CaseInvoice::class)
        ->between(
            start: $startOfMonth,
            end: $endOfMonth,
        )
        ->perDay()
        ->count();

        return [
            'datasets' => [
                [
                    'label' => 'নিষ্পত্তিকৃত মামালা',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#182ef1',
                    'backgroundColor' => 'rgba(29, 25, 245, 0.15)',
                    'tension' => 0.5,
                    'fill' => true,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 5,
                    'pointBackgroundColor' => '#e8e5f3',
                    'pointBorderColor' => '#182ef1',
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
