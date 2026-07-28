<?php

namespace App\Filament\Pages;

use App\Models\CaseInvoice;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Rakibhstu\Banglanumber\NumberToBangla;

class MonthlyTaxReport extends Page implements HasTable
{
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.monthly-tax-report';

    protected static ?string $title = 'মাসিক আদায় রিপোর্ট';
    protected static ?string $navigationLabel = 'মাসিক আদায় রিপোর্ট';
    protected static ?string $navigationGroup = 'রিপোর্টস';

    public ?array $formData = [];
    public ?int $month=null;
    public ?int $year=null;
    public ?float $total = null;
    public ?int $count = null;
    public ?int $office_id = null;
    public ?string $type = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('page_MonthlyTaxReport');
    }

    public function mount(): void
    {
        
        $this->year = now()->year;
        $this->month = now()->month;
        $this->type = 'yes';
    }

    public function en2bn($number): string
    {
        $en = ['0','1','2','3','4','5','6','7','8','9','January','February','March','April','May','June','July','August','September','October','November','December'];
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯','জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
        return str_replace($en, $bn, $number);
    }

    protected function getFormSchema(): array
    {
        $years = collect(range(now()->year , now()->year + 10))
            ->mapWithKeys(fn($year) => [$year => $year])
            ->toArray();

        return [
            Grid::make(5)
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
                        ->searchable()
                        ->options(function () {
                            return \App\Models\Office::pluck('name', 'id')->toArray();
                        })
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->resetTable())
                        ->placeholder('সকল ইউনিট'),
                    Select::make('type')
                        ->label('রিপোর্টের ধরন')
                        ->options([
                            'yes' => 'মওকুফসহ',
                            'no' => 'মওকুফবিহীন',
                        ])
                        ->default('yes')
                        ->reactive(),
                ]),
        ];
    }

    protected function getTableQuery()
    {
         $temp = CaseInvoice::query()
        ->when($this->month ?? null, fn ($query) => $query->where('month', $this->month))
        ->when($this->year ?? null, fn ($query) => $query->where('year', $this->year))
        ->when($this->office_id ?? null, fn ($query) => $query->where('office_id', $this->office_id));
        
        $this->total=$temp->sum('pay_amount');
        $this->count=$temp->count();

        return CaseInvoice::query()
        ->selectRaw('
            ROW_NUMBER() OVER (ORDER BY invoice_date) as id,
            invoice_date,
            month,
            year,
            COUNT(*) as total_invoices,
            SUM(mp_amount) as total_mp_amount,
            SUM(pay_amount) as total_amount
        ')
        ->when($this->month ?? null, fn ($query) => $query->where('month', $this->month))
        ->when($this->year ?? null, fn ($query) => $query->where('year', $this->year))
        ->when($this->office_id ?? null, fn ($query) => $query->where('office_id', $this->office_id))
        ->groupBy('invoice_date', 'month', 'year')
        
        ->orderBy('month', 'desc');
    }

    protected function getTableColumns(): array
    {
        $numto = new NumberToBangla();
        return [
            TextColumn::make('invoice_date')
                ->label('তারিখ')
                ->date('d-m-Y')
                ->sortable()
                ->formatStateUsing(function ($state) {
                    return $this->en2bn($state);
                }),
            TextColumn::make('month')
                ->label('মাস')
                ->sortable()
                ->formatStateUsing(function ($state) {
                    return $this->en2bn(date('F', mktime(0, 0, 0, $state, 1)) . '-' . $this->year);
                }),
            TextColumn::make('total_invoices')
                ->label('মামলার সংখ্যা')
                ->sortable()
                ->formatStateUsing(function ($state) use ($numto) {
                    return $numto->bnCommaLakh($state); 
                }),
            TextColumn::make('total_amount')
                ->label('আদায়')
                ->sortable()
                ->formatStateUsing(function ($state) use ($numto) {
                    return $numto->bnCommaLakh($state); 
                }),
            
            TextColumn::make('total_mp_amount')
                ->label('২৫% হার')
                ->sortable()
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
                    return route('monthly-tax-report.print', [
                        'month' => $this->month ?? null,
                        'year' => $this->year ?? null,
                        'office_id' => $this->office_id ?? null,
                        'type' => $this->type ?? null,
                    ]);
                })
                ->openUrlInNewTab(),
        ];
    }

      
}
