<?php

namespace App\Filament\Pages;

use App\Models\CaseInvoice;
use App\Models\Office;
use Carbon\Carbon;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Rakibhstu\Banglanumber\NumberToBangla;

class DailyTaxCollectorReport extends Page implements HasTable
{
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.daily-tax-collector-report';
    protected static ?string $title = 'দৈনিক জরিমানা আদাইয়ের রিপোর্ট';
    protected static ?string $navigationLabel = 'দৈনিক আদাইয়ের রিপোর্ট';

    protected static ?string $navigationGroup = 'রিপোর্টস';

    public ?Carbon $date = null;

    public ?string $today=null;
    public ?int $office=null;

   

    public static function canAccess(): bool
    {
        return auth()->user()?->can('page_DailyTaxCollectorReport');
    }

    public function mount(): void
    {
        $this->date = Carbon::now();
        $this->today = date('d-m-Y');
    }

    public function en2bn($number): string
    {
        $en = ['0','1','2','3','4','5','6','7','8','9','January','February','March','April','May','June','July','August','September','October','November','December'];
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯','জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
        return str_replace($en, $bn, $number);
    }

  protected function getFormSchema(): array
  {
        return [
            Grid::make(4)
                ->schema([
                    DatePicker::make('date')
                        ->label('তারিখ')
                        ->displayFormat('d-m-Y')
                        ->format('d-m-Y')
                        ->native(false)
                        ->closeOnDateSelection(true)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state) {
                           $this->today = $state->format('d-m-Y');
                            $this->resetTable();
                        }),
                    Select::make('office')
                        ->label('ইউনিট')
                        ->searchable()
                        ->options(function () {
                            return Office::pluck('name', 'id')->toArray();
                        })
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->resetTable())
                        ->required()
                        ->placeholder('সকল ইউনিট'),
                ]),
        ];
  }


    protected function getTableQuery()
    {
        return CaseInvoice::query()
            ->when($this->office ?? null, fn ($query) => $query->where('office_id', $this->office))
            ->when($this->today ?? null, fn ($query) => $query->where('invoice_date', $this->today))
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


    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('Print Report')
                ->label('রিপোর্ট প্রিন্ট করুন')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(function () {
                    return route('daily-tax-collector-report.print', [
                        'date' => $this->today,
                        'office' => $this->office ?? null,
                    ]);
                })
                ->openUrlInNewTab(),
        ];
    }


}
