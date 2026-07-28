<?php

namespace App\Filament\Pages;

use App\Models\CaseInvoice;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Rakibhstu\Banglanumber\NumberToBangla;

class YearlyFinancialReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.yearly-financial-report';

    protected static ?string $title = 'আর্থিক রিপোর্ট';
    protected static ?string $navigationLabel = 'আর্থিক রিপোর্ট';
    protected static ?string $navigationGroup = 'রিপোর্টস';

    public ?int $month = null;
    public ?int $year = null;

    public ?int $office_id=null;
   
    public ?float $total = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('page_YearlyFinancialReport');
    }

    public function mount(): void
    {
        $this->year = now()->year;
    }
    public function en2bn($number): string
    {
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $bn = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];
        return str_replace($en, $bn, $number);
    }

    protected function getFormSchema(): array
    {
        $years = collect(range(now()->year , now()->year + 10))
            ->mapWithKeys(fn($year) => [$year => $year])
            ->toArray();

        return [
            Grid::make(4)
                ->schema([
                    Select::make('month')
                        ->label('মাস')
                        ->options(array_combine(
                            range(1, 12),
                            array_map(fn($m) => date('F', mktime(0, 0, 0, $m, 1)), range(1, 12))
                        ))
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->resetTable()),

                    Select::make('year')
                        ->label('বছর')
                        ->options(function () {
                            $currentYear = date('Y');
                            $years = [];
                            for ($year = $currentYear; $year >= 2025; $year--) {
                                $years[$year] = $year;
                            }
                            return $years;
                        })
                        ->default(now()->year)
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->resetTable()),
                    Select::make('office_id')
                        ->label('ইউনিট')
                        ->options(\App\Models\Office::pluck('name', 'id')->toArray())
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->resetTable())
                        ->placeholder('সকল ইউনিট'),
                ]),
        ];
    }

    protected function getTableQuery()
    {
        $this->total = CaseInvoice::query()
        ->when($this->month ?? null, fn ($query) => $query->where('month', $this->month))
        ->when($this->year ?? null, fn ($query) => $query->where('year', $this->year))
         ->when($this->office_id ?? null, fn ($query) => $query->where('office_id', $this->office_id))
        ->sum('pay_amount');

        return CaseInvoice::query()
        ->selectRaw('
            ROW_NUMBER() OVER (ORDER BY invoice_date) as id,
            month,
            month_name,
            mp_percentage,
            year,
            office_id,
            SUM(pay_amount) as total_amount,
            SUM(mp_amount) as total_mp_amount,
            SUM(board_amount) as total_board_amount'
            
            )
        ->when($this->month ?? null, fn ($query) => $query->where('month', $this->month))
        ->when($this->year ?? null, fn ($query) => $query->where('year', $this->year))
         ->when($this->office_id ?? null, fn ($query) => $query->where('office_id', $this->office_id))
        ->groupBy( 'month', 'month_name', 'year','mp_percentage','office_id')
        ->orderBy('month', 'asc');
    }

    protected function getTableColumns(): array
    {
        $numto = new NumberToBangla();
        return [
            TextColumn::make('month_name')
                ->label('মাস')
                ->formatStateUsing(fn ($state) => $this->en2bn($state))
                ->sortable()
                ->searchable(),
            TextColumn::make('year')
                ->label('বছর')
                ->formatStateUsing(fn ($state) => $this->en2bn($state))
                ->sortable()
                ->searchable(),
            TextColumn::make('total_amount')
                ->label('মোট পরিমাণ')
                ->formatStateUsing(function ($state) use ($numto) {
                    return $numto->bnCommaLakh($state); 
                })
                ->sortable()
                ->searchable(),
            TextColumn::make('mp_percentage')
                ->label('এমপি %')
                ->formatStateUsing(fn ($state) => $this->en2bn($state)),
            TextColumn::make('total_mp_amount')
                ->label('এমপি টাকা')
               ->formatStateUsing(function ($state) use ($numto) {
                    return $numto->bnCommaLakh($state); 
                }),
             TextColumn::make('total_board_amount')
                ->label('বোর্ড টাকা')
               ->formatStateUsing(function ($state) use ($numto) {
                    return $numto->bnCommaLakh($state); 
                }), 
            
            
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('Print Report')
                ->label('রিপোর্ট প্রিন্ট করুন')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(function () {
                    return route('yearly-financial-report.print', [
                        'month' => $this->month ?? null,
                        'year' => $this->year ?? null,
                        'office_id' => $this->office_id ?? null,
                    ]);
                })
                ->openUrlInNewTab(),
        ];
    }
}

