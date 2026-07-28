<?php

namespace App\Filament\Pages;

use App\Models\Lawsuit;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Rakibhstu\Banglanumber\NumberToBangla;
class UnpaidCaseReport extends Page implements HasTable
{
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.unpaid-case-report';

     protected static ?string $title = 'অনিষ্পন্ন মামলার রিপোর্ট';
    protected static ?string $navigationLabel = 'অনিষ্পন্ন মামলা রিপোর্ট';

    protected static ?string $navigationGroup = 'রিপোর্টস';

    public ?string $date=null;
    public ?int $month=null;
    public ?string $year=null;
    public ?string $type=null;
    public ?int $office_id=null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('page_UnpaidCaseReport');
    }

    public function mount(): void
    {
        
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
            Grid::make(4)
                ->schema([
                    Select::make('type')
                    ->label('ধরন')
                    ->reactive()
                    ->options([
                        'daily'=>'দৈনিক',
                        'monthly'=>'মাসিক',
                        'yearly'=>'বার্ষিক'
                    ])
                    ->afterStateUpdated(function($state,$set,$get){
                        $this->date=null;
                        $this->month=null;
                        $this->year=null;
                    }),
                    DatePicker::make('date')
                        ->label('তারিখ')
                        ->displayFormat('d-m-Y')
                        ->format('d-m-Y')
                        ->native(false)
                        ->closeOnDateSelection(true)
                        ->required()
                        ->reactive()
                        ->visible(fn ($get) => $get('type')==='daily')
                        ->afterStateUpdated(function ($state) {
                            $this->resetTable();
                        }),
                    Select::make('month')
                        ->label('মাস')
                        ->options(array_combine(
                            range(1, 12),
                            array_map(fn($m) => date('F', mktime(0, 0, 0, $m, 1)), range(1, 12))
                        ))
                        ->visible(fn ($get) => $get('type')==='monthly')
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->resetTable()),

                    Select::make('year')
                        ->label('বছর')
                        ->options($years)
                        ->default(now()->year)
                        ->reactive()
                        ->visible(fn ($get) => $get('type')==='monthly' || $get('type')==='yearly')
                        ->afterStateUpdated(fn () => $this->resetTable()),
                    Select::make('office_id')
                        ->label('ইউনিট')
                        ->options(function () {
                            return \App\Models\Office::pluck('name', 'id')->toArray();
                        })
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->resetTable())
                        ->placeholder('সকল ইউনিট'),
                ]),
        ];
    }

    protected function getTableQuery()
    {
        return Lawsuit::query()
        ->when($this->date ?? null, fn ($query) => $query->where('lawsuit_date', date('d-m-Y',strtotime($this->date))))
        ->when($this->month ?? null, fn ($query) => $query->where('month', $this->month))
        ->when($this->year ?? null, fn ($query) => $query->where('year', $this->year))
        ->when($this->office_id ?? null, fn ($query) => $query->where('office_id', $this->office_id))
        ->where(['status'=>'Unpaid','case_status'=>'approved']);
    }

     protected function getTableColumns(): array
    {
        $numto = new NumberToBangla();
        return [
            TextColumn::make('lawsuit_date')
                ->label('মামলার তারিখ')
                ->date('d-m-Y')
                ->sortable()
                ->formatStateUsing(function ($state) {
                    return $this->en2bn($state);
                }),

            TextColumn::make('probable_date')
                ->label('নিষ্পত্তির তারিখ')
                ->date('d-m-Y')
                ->sortable()
                ->formatStateUsing(function ($state) {
                    return $this->en2bn($state);
                }),

            TextColumn::make('vechicle_number')
            ->label('গাড়ীর নাম্বার'),
            
            // TextColumn::make('days_difference')
            //     ->label('দিন')
            //     ->getStateUsing(function ($record) {
            //             $probableDate = Carbon::createFromFormat('d-m-Y', $record->probable_date);
            //             $diff = now()->startOfDay()->diffInDays($probableDate->startOfDay(), false);
            //             // Convert number to Bangla
            //             $banglaDiff = $this->en2bn((string) intval($diff));

            //             // Return formatted text
            //             return $diff < 0
            //                 ? "বিলম্ব {$banglaDiff} দিন" // e.g. "Overdue X days"
            //                 : "{$banglaDiff} দিন বাকি";
            //     })
            //     ->color(function ($record) {
            //                 $diff = now()->diffInDays(Carbon::createFromFormat('d-m-Y', $record->probable_date), false);
            //                 return $diff < 0 ? 'danger' : 'success'; // danger = red, success = green
            //             }),
                
            TextColumn::make('month')
                ->label('মাস')
                ->sortable()
                ->formatStateUsing(function ($state) {
                    return $this->en2bn(date('F', mktime(0, 0, 0, $state, 1)));
                }),
            TextColumn::make('year')
                ->label('বছর')
                ->sortable()
                ->formatStateUsing(function ($state) {
                    return $this->en2bn($state);
                }),
            TextColumn::make('total_amount')
                ->label('জরিমানার পরিমান')
                ->sortable()
                ->formatStateUsing(function ($state) use ($numto) {
                    return $numto->bnNum($state);
                }),
            TextColumn::make('status')
                ->label('মন্তব্য')
                ->getStateUsing( function($record){
                    return 'অনিষ্পন্ন';
                })
                ->color(function($record){
                    return 'danger';
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
                    return route('unpaid-case-report.print', [
                        'date' => $this->date ??null,
                        'month' => $this->month ?? null,
                        'year' => $this->year ?? null,
                        'office_id' => $this->office_id ?? null,
                    ]);
                })
                ->openUrlInNewTab(),
        ];
    }

}
