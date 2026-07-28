<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Doctrine\DBAL\Schema\Index;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;


class SentCaseReport extends Page implements HasTable
{
    use InteractsWithTable;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.sent-case-report';
    protected static ?string $title = 'প্রেরিত মামলার রিপোর্ট';
    protected static ?string $navigationLabel = 'প্রেরিত মামলার রিপোর্ট';
    protected static ?string $navigationGroup = 'রিপোর্টস';

    public ?Carbon $date = null;
    public ?Carbon $end_date = null;
    public ?string $today = null;
    public ?int $user_id = null;
    public ?int $office_id = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('page_SentCaseReport');
    }

    public function mount(): void
    {
        $this->date = now();
        $this->end_date = null;
        
    }
    
    function en2bn($number): string
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
                        ->label('তারিখ থেকে')
                        ->displayFormat('d-m-Y')
                        ->native(false)
                        ->required()
                        ->reactive()
                        ->closeOnDateSelection()
                        ->afterStateUpdated(fn () => $this->resetTable()),

                    DatePicker::make('end_date')
                        ->label('তারিখ পর্যন্ত')
                        ->displayFormat('d-m-Y')
                        ->native(false)
                        ->reactive()
                        ->closeOnDateSelection()
                        ->afterStateUpdated(fn () => $this->resetTable()),
                        
                    Select::make('office_id')
                        ->label('ইউনিট')
                        ->options(\App\Models\Office::pluck('name', 'id'))
                        ->visible(auth()->user()->hasRole('super_admin'))
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->resetTable())
                        ->placeholder('সকল ইউনিট'),
                    Select::make('user_id')
                        ->label('ইউজার')
                        ->options(\App\Models\User::where('office_id', auth()->user()->office_id)->pluck('name', 'id'))
                        ->visible(!auth()->user()->hasRole('super_admin'))
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(fn () => $this->resetTable())
                        ->placeholder('সকল ইউজার'),
                ]),
        ];
    }

    protected function getTableQuery()
    {
        return \App\Models\Lawsuit::query()
        ->where('case_status', 'approved')

        ->when($this->date && $this->end_date, function ($query) {
            $query->whereRaw(
                "STR_TO_DATE(approval_date, '%d-%m-%Y') BETWEEN ? AND ?",
                [
                    $this->date->format('Y-m-d'),
                    $this->end_date->format('Y-m-d'),
                ]
            );
        })

        ->when($this->date && !$this->end_date, function ($query) {
            $query->where('approval_date', $this->date->format('d-m-Y'));
        })
        ->when($this->user_id, function ($query) {
            $query->where('entry_user_id', $this->user_id);
        })
        ->when(
            auth()->user()->hasRole('super_admin'),
            fn ($query) => $query->when(
                $this->office_id,
                fn ($q) => $q->where('office_id', $this->office_id)
            ),
            fn ($query) => $query->where('office_id', auth()->user()->office_id)
        );
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('sl')
                ->label('ক্রমিক নং')
            //   ->state(function ($record, $livewire) {
            //         // Current page and per page
            //         $page = $livewire->getTablePage();
            //         $perPage = $livewire->getTableRecordsPerPage();

            //         // Index of the record in current page (0-based)
            //         $records = $livewire->getTableRecords();
            //         $index = $records->search(fn ($r) => $r->getKey() === $record->getKey());

            //         return $this->en2bn((($page - 1) * $perPage) + $index + 1);
            //     })
                ->rowIndex()
                ->sortable(false)
                ->searchable(false),
            TextColumn::make('lawsuit_date')
                ->label('অনিয়মের তারিখ')
                ->sortable()
                ->searchable()
                ->formatStateUsing(fn ($state) => $this->en2bn($state)),
            TextColumn::make('vechicle_number')
                ->label('গাড়ির নম্বর')
                ->sortable()
                ->searchable()
                ->formatStateUsing(fn ($state) => $this->en2bn($state)),
            TextColumn::make('vechicleCategory.title')
                ->label('যানবাহনের প্রকার')
                ->sortable()
                ->searchable(),
            TextColumn::make('location')
                ->label('অনিয়মের স্থান')
                ->sortable()
                ->searchable(),
            TextColumn::make('lawsuitSections.section.title')
                ->label('অনিয়মের ধরণ')
                ->sortable()
                ->searchable(),
            TextColumn::make('lawsuitDocuments.document.title')
                ->label('আটকৃত নথি')
                ->sortable()
                ->searchable(),
            TextColumn::make('total_amount')
                ->label('মোট জরিমানা')
                ->sortable()
                ->searchable()
                ->formatStateUsing(fn ($state) => $this->en2bn($state)),
            TextColumn::make('approval_date')
                ->label('প্রেরিত হয়েছে')
                ->sortable()
                ->searchable()
                ->formatStateUsing(fn ($state) => $this->en2bn($state)),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            ExportAction::make('ExportExcel')
                ->label('এক্সেলে ডাউনলোড')
                ->color('success')
                ->exports([
                    ExcelExport::make()
                        ->fromTable()
                        ->withFilename('প্রেরিত মামলার তালিকা_' . now()->format('Y-m-d'))
                        ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                        ->withColumns([
                            Column::make('sl')
                                ->heading('ক্রমিক নং')
                                ->getStateUsing(function () {
                                    static $i = 0;
                                    return $this->en2bn(++$i); // Bangla serial
                                }),

                            Column::make('lawsuit_date')
                                ->heading('অনিয়মের তারিখ')
                                ->getStateUsing(fn ($record) => $this->en2bn($record->lawsuit_date)),

                            Column::make('vechicle_number')
                                ->heading('গাড়ির নম্বর')
                                ->getStateUsing(fn ($record) => $this->en2bn($record->vechicle_number)),

                            Column::make('vechicleCategory.title')
                                ->heading('যানবাহনের প্রকার'),

                            Column::make('location')
                                ->heading('অনিয়মের স্থান'),

                            Column::make('lawsuitSections.section.title')
                                ->heading('অনিয়মের ধরণ'),

                            Column::make('lawsuitDocuments.document.title')
                                ->heading('আটকৃত নথি'),

                            Column::make('total_amount')
                                ->heading('মোট জরিমানা')
                                ->getStateUsing(fn ($record) => $this->en2bn($record->total_amount)),

                            Column::make('approval_date')
                                ->heading('প্রেরিত হয়েছে')
                                ->getStateUsing(fn ($record) => $this->en2bn($record->approval_date)),
                        ]),          
                ]),
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
                    return route('daily-sent-case-report.print', [
                        'date' => $this->date ? $this->date->format('Y-m-d') : null,
                        'end_date' => $this->end_date ? $this->end_date->format('Y-m-d') : null,
                        'user_id' => $this->user_id,
                        'office_id' => auth()->user()->hasRole('super_admin') ? $this->office_id : auth()->user()->office_id,
                        'status'=>'approved',
                    ]);
                })
                ->openUrlInNewTab(),
        ];
    }

    protected function shouldPersistTablePaginationInQueryString(): bool
    {
        return false;
    }
}
