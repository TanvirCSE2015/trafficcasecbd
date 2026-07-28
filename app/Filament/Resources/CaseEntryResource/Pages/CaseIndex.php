<?php

namespace App\Filament\Resources\CaseEntryResource\Pages;

use App\Filament\Resources\CaseEntryResource;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action as ActionsAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use GuzzleHttp\Promise\Create;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class CaseIndex extends Page implements HasTable, HasForms
{
    use InteractsWithTable,InteractsWithForms;
    protected static string $resource = CaseEntryResource::class;

    protected static string $view = 'filament.resources.case-entry-resource.pages.case-index';

    public ?Carbon $date = null;
    public ?Carbon $end_date = null;
    public ?int $user_id=null;

    public function en2bn($number): string
    {
        $en = ['0','1','2','3','4','5','6','7','8','9','January','February','March','April','May','June','July','August','September','October','November','December'];
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯','জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
        return str_replace($en, $bn, $number);
    }

    public function mount(): void
    {
        $this->user_id = auth()->user()->user_type=='board' ? auth()->user()->id : null;
    }

    protected function getFormSchema(): array
    {
       return[ 
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
            Select::make('user_id')
                ->label('ইনট্রি ইউজার')
                ->options(\App\Models\User::where(['user_type'=> auth()->user()->user_type,'office_id'=> auth()->user()->office_id])
                    ->pluck('name', 'id'))
                ->visible(fn () => auth()->user()->user_type=='board')
                ->reactive()
                ->afterStateUpdated(fn () => $this->resetTable()),
        ]) 
       ];
    }

    protected function getTableQuery(): Builder|Relation|null
    {
       return \App\Models\Lawsuit::query()
            ->where(['office_id'=> auth()->user()->office_id, 'case_status' => 'pending'])
            ->when($this->date && $this->end_date, function ($query) {
                $query->whereRaw(
                    "STR_TO_DATE(lawsuit_date, '%d-%m-%Y') BETWEEN ? AND ?",
                    [
                        $this->date->format('Y-m-d'),
                        $this->end_date->format('Y-m-d'),
                    ]
                );
            })
            ->when($this->date && !$this->end_date, function ($query) {
                $query->where('lawsuit_date', $this->date->format('d-m-Y'));
            })
            ->when($this->user_id, function ($query) {
                $query->where('entry_user_id', $this->user_id);
            });
    }

    protected function getTableColumns(): array
    {
        return [

            TextColumn::make('sl_no')
                    ->label('ক্রমিক নং')
                //     ->state(function ($record, $livewire) {
                //     // Current page and per page
                //     $page = $livewire->getTablePage();
                //     $perPage = $livewire->getTableRecordsPerPage();

                //     // Index of the record in current page (0-based)
                //     $records = $livewire->getTableRecords();
                //     $index = $records->search(fn ($r) => $r->getKey() === $record->getKey());

                //     return self::en2bn((($page - 1) * $perPage) + $index + 1);
                // })
                    ->rowIndex()
                    ->sortable(false)
                    ->searchable(false),
            TextColumn::make('lawsuit_date')
                    ->label('অনিয়মের তারিখ')
                    ->searchable()
                     ->formatStateUsing(function ($state) {
                       return $state ? self::en2bn($state) : '';
                    })
                    ->sortable(),
            TextColumn::make('vechicle_number')
                   ->label('গাড়ীর নাম্বার')
                    ->searchable(),
            TextColumn::make('case_status')
                ->label('মন্তব্য')
                ->badge()
                ->color('warning'),
            TextColumn::make('vechicleCategory.title')
                    ->label('গাড়ীর ধরণ')
                    ->searchable(),
            TextColumn::make('location')
                    ->label('অপরাধের স্থান'),
            TextColumn::make('lawsuitSections.section.title')
                    ->label('অপরাধের বিবরণ')
                    ->getStateUsing(function ($record) {
                        return $record->lawsuitSections->pluck('section.title')->join(', ');
                    }),
            TextColumn::make('lawsuitDocuments.document.title')
                ->label('আটকৃত নথি')
                ->sortable()
                ->searchable(),
                // Tables\Columns\TextColumn::make('office.name')
                //     ->label('অফিস')
                //     ->numeric()
                //     ->sortable(),
            TextColumn::make('created_at')
                    ->label('তৈরির তারিখ')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
                    ->label('হালনাগাদের তারিখ')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

        ];
    }

    public function getTitle(): string
    {
        return 'মামলার তালিকা';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('নতুন মামলা যোগ করুন')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(fn () => CaseEntryResource::getUrl('create')),

            Action::make('Approve')
                ->label('মামলা প্রেরণ করুন')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('নিশ্চিতকরণ প্রয়োজন')
                ->modalDescription('আপনি কি নিশ্চিত যে আপনি এই মামলাটি প্রেরণ করতে চান?')
                ->modalSubmitActionLabel('হ্যাঁ, প্রেরণ করুন')
                ->modalCancelActionLabel('না, বাতিল করুন')
                ->action(function () {
                    $query=\App\Models\Lawsuit::query()
                        ->where(['office_id'=> auth()->user()->office_id, 'case_status' => 'pending'])
                        ->when($this->date && $this->end_date, function ($query) {
                            $query->whereRaw(
                                "STR_TO_DATE(lawsuit_date, '%d-%m-%Y') BETWEEN ? AND ?",
                                [
                                    $this->date->format('Y-m-d'),
                                    $this->end_date->format('Y-m-d'),
                                ]
                            );
                        })
                        ->when($this->date && !$this->end_date, function ($query) {
                            $query->where('lawsuit_date', $this->date->format('d-m-Y'));
                        })
                        ->when($this->user_id, function ($query) {
                            $query->where('entry_user_id', $this->user_id);
                        });
                    // if (auth()->user()->user_type === 'board') {
                    //     $query->where('entry_user_id', auth()->user()->id);
                    // }

                    $query->update([
                        'case_status'   => 'approved',
                        'approval_date' => now()->format('d-m-Y'),
                    ]);
                     Notification::make()
                        ->title('মামলা সফলভাবে প্রেরণ করা হয়েছে।')
                        ->success()
                        ->send();
                     $this->dispatch('refreshList');
                })
                ->visible(fn () => auth()->user()->hasRole('mp_admin')),

        ];
    }

    protected function getTableActions(): array
    {
        return [
            ActionsAction::make('edit')
                ->label('সম্পাদনা করুন')
                ->icon('heroicon-o-pencil')
                ->color('primary')
                ->url(fn (\App\Models\Lawsuit $record) => CaseEntryResource::getUrl('edit', ['record' => $record])),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
                ActionsAction::make('Print Report')
                    ->label('রিপোর্ট প্রিন্ট করুন')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(function () {
                        return route('daily-sent-case-report.print', [
                            'date' => $this->date ? $this->date->format('Y-m-d') : null,
                            'end_date' => $this->end_date ? $this->end_date->format('Y-m-d') : null,
                            'status'=>'pending',
                            'office_id' => auth()->user()->hasRole('super_admin') ? '' : auth()->user()->office_id,
                        ]);
                    })
                ->openUrlInNewTab(),
                ExportAction::make('ExportExcel')
                    ->label('এক্সেলে ডাউনলোড')
                    ->color(Color::Cyan)
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('প্রেরিত মামলার তালিকা_' . now()->format('Y-m-d'))
                            ->withWriterType(\Maatwebsite\Excel\Excel::XLSX) 
                            ->withColumns([
                                Column::make('sl_no')
                                    ->heading('ক্রমি নং')
                                    ->getStateUsing(function () {
                                        static $i = 0;
                                        return self::en2bn(++$i); // Bangla serial
                                    }),

                                Column::make('lawsuit_date')
                                    ->heading('অনিয়মের তারিখ')
                                    ->getStateUsing(fn ($record) => self::en2bn($record->lawsuit_date)),

                                Column::make('vechicle_number')
                                    ->heading('গাড়ির নম্বর')
                                    ->getStateUsing(fn ($record) => self::en2bn($record->vechicle_number)),
                                Column::make('vechicleCategory.title')
                                    ->heading('যানবাহনের প্রকার'),

                                Column::make('location')
                                    ->heading('অনিয়মের স্থান'),
                                Column::make('lawsuitSections.section.title')
                                    ->heading('অনিয়মের ধরণ'),

                                Column::make('lawsuitDocuments.document.title')
                                    ->heading('আটকৃত নথি'),

                                Column::make('total_amount')
                                    ->heading('মোট জরিমানা')
                                    ->getStateUsing(fn ($record) => self::en2bn($record->total_amount)),

                            // ColumnsColumn::make('approval_date')
                            //     ->heading('প্রেরিত হয়েছে')
                            //     ->getStateUsing(fn ($record) => self::en2bn($record->approval_date)),
                        ]),         
                ]),

        ];
    }

}
