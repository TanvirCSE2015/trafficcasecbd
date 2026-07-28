<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CaseEntryResource\Pages;
use App\Filament\Resources\CaseEntryResource\RelationManagers;
use App\Models\CaseEntry;
use App\Models\Lawsuit;
// use Doctrine\DBAL\Schema\Column;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class CaseEntryResource extends Resource
{
    protected static ?string $model = CaseEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil';

    protected static ?string $navigationLabel="মামলা এন্ট্রি";

    public static function en2bn($number): string
    {
        $en = ['0','1','2','3','4','5','6','7','8','9','January','February','March','April','May','June','July','August','September','October','November','December'];
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯','জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
        return str_replace($en, $bn, $number);
    }
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('vechicle_number')
                    ->label('গাড়ীর রেজিস্ট্রেশন নম্বর')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(3),
                Forms\Components\DatePicker::make('lawsuit_date')
                    ->label('মামলার তারিখ')
                    ->displayFormat('d-m-Y')
                    ->native(false)
                    ->closeOnDateSelection(true)
                    ->format('d-m-Y')
                    ->maxDate(now())
                    ->required()
                    ->columnSpan(2)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        // Automatically set the invoice number when the lawsuit date is set
                        if ($state) {
                            $set('box_no', date('d-m-Y', strtotime($state)));
                        }
                    }),
                Forms\Components\DatePicker::make('probable_date')
                    ->label('নিষ্পত্তির তারিখ')
                    ->displayFormat('d-m-Y')
                    ->native(false)
                    ->closeOnDateSelection(true)
                    ->format('d-m-Y')
                    ->required()
                    ->columnSpan(2),
                // Forms\Components\TextInput::make('vehicle_type')
                //     ->label('গাড়ীর ধরন')
                //     ->required()
                //     ->maxLength(255)
                //     ->default(null)
                //     ->columnSpan(3),
                Select::make('vehicle_type')
                    ->label('গাড়ীর ধরণ')
                    ->options(\App\Models\VehicleCategory::pluck('title', 'id'))
                    ->preload()
                    ->required()
                    ->searchable()
                    ->columnSpan(3),
                Forms\Components\TextInput::make('location')
                    ->label('অপরাধের স্থান')
                    ->required()
                    ->maxLength(255)
                    ->default(null)
                    ->columnSpan(2),
                Forms\Components\TextInput::make('total_amount')
                    ->numeric()
                    ->reactive()
                    ->visible(false)
                    ->dehydrated(true),

                Forms\Components\TextInput::make('box_no')
                    ->maxLength(255)
                    ->visible(false)
                    ->reactive()
                    ->dehydrated(true),
                Select::make('lawsuitSections')
                    ->label('অপরাধের বিবরণ')
                    ->options(\App\Models\Section::query()->orderBy('id', 'asc')
                            ->selectRaw("id, CONCAT(section_no, ' । ', title) as label")
                            ->pluck('label', 'id')
                    )
                    ->preload()
                    ->multiple()
                    ->required()
                    ->searchable()
                    ->columnSpan(6)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // $state is an array of selected section IDs
                        $total = \App\Models\Section::whereIn('id', $state)->sum('amount');
                        $set('total_amount', $total);
                    }),
                Select::make('lawsuitDocuments')
                    ->options(\App\Models\Document::query()
                        ->orderBy('id', 'asc')
                        ->get()
                        ->mapWithKeys(fn ($document) => [
                                $document->id =>  self::en2bn($document->id) . ' । ' . $document->title,
                            ])
                            ->toArray()
                        )
                    ->preload()
                    ->multiple()
                    ->searchable()
                    ->label('জব্দকৃত কাগজপত্র')
                    ->required()
                    ->columnSpan(6),

            ])->columns(12);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(['office_id'=> auth()->user()->office_id, 'case_status' => 'pending']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                
               Tables\Columns\TextColumn::make('sl_no')
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
                Tables\Columns\TextColumn::make('lawsuit_date')
                    ->label('অনিয়মের তারিখ')
                    ->searchable()
                     ->formatStateUsing(function ($state) {
                       return $state ? self::en2bn($state) : '';
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('vechicle_number')
                   ->label('গাড়ীর নাম্বার')
                    ->searchable(),
                Tables\Columns\TextColumn::make('case_status')
                ->label('মন্তব্য')
                ->badge()
                ->color('warning'),
                Tables\Columns\TextColumn::make('vechicleCategory.title')
                    ->label('গাড়ীর ধরণ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('অপরাধের স্থান'),
                Tables\Columns\TextColumn::make('lawsuitSections.section.title')
                    ->label('অপরাধের বিবরণ'),
                Tables\Columns\TextColumn::make('lawsuitDocuments.document.title')
                ->label('আটকৃত নথি')
                ->sortable()
                ->searchable(),
                // Tables\Columns\TextColumn::make('office.name')
                //     ->label('অফিস')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('তৈরির তারিখ')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('হালনাগাদের তারিখ')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->defaultSort('created_at', 'asc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('Print Report')
                    ->label('রিপোর্ট প্রিন্ট করুন')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(function () {
                        return route('daily-sent-case-report.print', [
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
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Lawsuit::where(['case_status' => 'pending','office_id' => auth()->user()->office_id])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\CaseIndex::route('/'),
            'create' => Pages\CreateCaseEntry::route('/create'),
            'edit' => Pages\EditCaseEntry::route('/{record}/edit'),
        ];
    }
}
