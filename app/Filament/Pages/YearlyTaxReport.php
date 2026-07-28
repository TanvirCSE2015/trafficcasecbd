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

class YearlyTaxReport extends Page implements HasTable
{
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.yearly-tax-report';
    protected static ?string $title = 'বার্ষিক আদায় রিপোর্ট';
    protected static ?string $navigationLabel = 'বার্ষিক আদায় রিপোর্ট';
    protected static ?string $navigationGroup = 'রিপোর্টস';

    
    public ?int $year=null;
    public ?float $total = null;
    public ?int $office_id = null;
    public static function canAccess(): bool
    {
        return auth()->user()?->can('page_YearlyTaxReport');
    }
    public function mount(): void
    {
        $this->year = now()->year;
    }

    public function en2bn($number): string
    {
        $en = ['0','1','2','3','4','5','6','7','8','9','January','February','March','April','May','June','July','August','September','October','November','December'];
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯','জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
        return str_replace($en, $bn, $number);
    }

    protected function getFormSchema(): array
    {
        $years = collect(range(now()->year - 1, now()->year + 15))
            ->mapWithKeys(fn($year) => [$year => $year])
            ->toArray();

        return [
             Grid::make(3)
                ->schema([
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
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->resetTable())
                        ->default(now()->year)
                        ->required(),
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
        ->when($this->year ?? null, fn ($query) => $query->where('year', $this->year))
        ->when($this->office_id ?? null, fn ($query) => $query->where('office_id', $this->office_id))
        ->sum('pay_amount');
        return CaseInvoice::query()
            ->selectRaw('
                ROW_NUMBER() OVER (ORDER BY month) as id,
                month,
                month_name,
                year,
                office_id,
                SUM(pay_amount) as total_amount
            ')
            ->when($this->year, fn ($query) => $query->where('year', $this->year))
            ->when($this->office_id ?? null, fn ($query) => $query->where('office_id', $this->office_id))
            ->groupBy( 'month', 'month_name', 'year', 'office_id')
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
            TextColumn::make('office.name')
                ->label('ইউনিট')
                ->sortable()
                ->searchable(),
            TextColumn::make('total_amount')
                ->label('মোট আদায়')
                ->formatStateUsing(function ($state) use ($numto) {
                    return $numto->bnCommaLakh($state); 
                })
                ->sortable()
                ->searchable(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('print')
                ->label('প্রিন্ট রিপোর্ট')
                ->url(fn () => route('yearly-tax-report.print', [
                    'year' => $this->year, 
                    'office_id' => $this->office_id ?? null,
                    ]))
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->openUrlInNewTab(),
        ];
    }
}
