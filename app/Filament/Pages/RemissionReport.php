<?php

namespace App\Filament\Pages;

use App\Models\CaseInvoice;
use App\Models\Office;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\DB;
use Rakibhstu\Banglanumber\NumberToBangla;

class RemissionReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.remission-report';

    protected static ?string $title = 'অব্যাহতি ও মওকুফ মামলার রিপোর্ট';
    protected static ?string $navigationLabel = 'মওকুফ মামলার রিপোর্ট';

    protected static ?string $navigationGroup = 'রিপোর্টস';

    public ?Carbon $from = null;
    public ?Carbon $to = null;
    public ?int $office=null;
    // public ?string $fromDate=null;
    // public ?string $toDate=null;
    public ?string $status = null;
    
    public static function canAccess(): bool
    {
        return auth()->user()?->can('page_RemissionReport');
    }

    public function en2bn($number): string
    {
        $en = ['0','1','2','3','4','5','6','7','8','9','January','February','March','April','May','June','July','August','September','October','November','December'];
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯','জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
        return str_replace($en, $bn, $number);
    }

    public function mount(): void
    {
        $this->from = Carbon::now()->startOfMonth();
        $this->to = Carbon::now();
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(5)
                ->schema([
                    DatePicker::make('from')
                        ->label('তারিখ (থেকে)')
                        ->displayFormat('d-m-Y')
                        ->native(false)
                        ->closeOnDateSelection(true)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->resetTable()),

                    DatePicker::make('to')
                        ->label('তারিখ (পর্যন্ত)')
                        ->displayFormat('d-m-Y')
                        ->native(false)
                        ->closeOnDateSelection(true)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->resetTable()),
                    Select::make('office')
                        ->label('অফিস')
                        ->options(Office::all()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->resetTable()),
                    Select::make('status')
                        ->label('স্ট্যাটাস')
                        ->options([
                            'remission' => 'মওকুফ',
                            'Released' => 'অব্যাহতি',
                        ])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->resetTable()),
                ]),
        ];
    }

    protected function getTableQuery()
    {
        return CaseInvoice::query()
            ->when($this->office ?? null, fn ($query) => $query->where('office_id', $this->office))
            ->when($this->from && $this->to, function ($query) {
                $query->whereBetween(
                    DB::raw("STR_TO_DATE(invoice_date, '%d-%m-%Y')"),
                    [
                        $this->from->format('Y-m-d'),
                        $this->to->format('Y-m-d'),
                    ]
                );
            })
            ->when(
                $this->status,
                function ($query) {
                    if ($this->status === 'Released') {
                        $query->where('status', 'Released');
                    } elseif ($this->status === 'remission') {
                        $query->where('discount_amount', '>', 0);
                    }
                },
                function ($query) {
                    // No status selected
                    $query->where(function ($q) {
                        $q->where('status', 'Released')
                        ->orWhere('discount_amount', '>', 0);
                    });
                }
            )
            ->with(['lawsuit.lawsuitSections.section'])
            ->orderBy('invoice_date', 'desc');
    }

    protected function getTableColumns(): array
    {
    
        $numto = new NumberToBangla();
        return [
            //  TextColumn::make('sl_no')
            //     ->label('ক্রমিক নং')
            //     ->rowIndex()
            //     ->formatStateUsing(fn ($state) => self::en2bn($state))
            //     ->sortable(false)
            //     ->searchable(false),
            TextColumn::make('invoice_date')
                ->label('তারিখ')
                ->date('d-m-Y')
                ->sortable()
                ->formatStateUsing(function ($state) {
                    return $this->en2bn($state); 
                }),
            TextColumn::make('car_no')
                ->label('গাড়ির নাম্বার')
                ->searchable()
                ->sortable()
                ->formatStateUsing(function ($state) {
                    return $this->en2bn($state); 
                }),
            TextColumn::make('lawsuit.lawsuitSections.section.section_no')
                ->label('অপরাধের ধারা')
                ->sortable()
                ->formatStateUsing(function ($state) {
                    return $this->en2bn($state); 
                }),
            TextColumn::make('total_amount')
                ->label('মোট পরিমাণ')
                ->money('bdt')
                ->formatStateUsing(function ($state) use ($numto) {
                    return $numto->bnCommaLakh($state); 
                })
                ->sortable(),
            TextColumn::make('discount')
                ->label('ছাড় (%)')
                ->formatStateUsing(function ($state) {
                    return $this->en2bn($state) . ' %'; 
                })
                ->sortable(),
            TextColumn::make('discount_amount') 
                ->label('ছাড় পরিমাণ')
                ->money('bdt')
                ->formatStateUsing(function ($state) use ($numto) {
                    return $numto->bnCommaLakh($state); 
                })
                ->sortable(),
           
            TextColumn::make('pay_amount')
                ->label('আদায়')
                ->money('bdt')
                ->formatStateUsing(function ($state)  use ($numto) {
                    return $numto->bnCommaLakh($state); 
                })
                ->sortable(),

            TextColumn::make('status')
                ->label('স্ট্যাটাস')
                ->badge()
                ->color('success')
                ->sortable(),
            TextColumn::make('invoice_number')
                ->label('রশিদ নং')
                
                ->formatStateUsing(function ($state)  use ($numto) {
                    return $this->en2bn($state); 
                })
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Action::make('Print Report')
                ->label('রিপোর্ট প্রিন্ট করুন')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(function () {
                    return route('remission-report.print', [
                        'from' => $this->from->toDateString(),
                        'to' => $this->to->toDateString(),
                        'office' => $this->office ?? null,
                        'status' => $this->status ?? null,
                    ]);
                })
                ->openUrlInNewTab(),
        ];
    }
}
