<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LawsuitResource\Pages;
use App\Filament\Resources\LawsuitResource\RelationManagers;
use App\Models\Lawsuit;
use Dom\Text;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LawsuitResource extends Resource
{
    protected static ?string $model = Lawsuit::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?string $navigationLabel="আগত মামলা";

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
                    ->label('গাড়ীর নম্বর')
                    ->required()
                    ->maxLength(255)
                    ->readOnly(fn ($record) => in_array($record?->status, ['Paid', 'Released']))
                    ->columnSpan(3)
                    ->formatStateUsing(function ($state) {
                       return $state ? self::en2bn($state) : '';
                    }),
                Forms\Components\DatePicker::make('lawsuit_date')
                    ->label('মামলার তারিখ')
                    ->native(false)
                    ->closeOnDateSelection(true)
                    ->format('d-m-Y')
                    ->displayFormat('d-m-Y')
                    ->maxDate(now())
                    ->required()
                    ->readOnly(fn ($record) => in_array($record?->status, ['Paid', 'Released']))
                    ->reactive()

                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        // Automatically set the invoice number when the lawsuit date is set
                        if ($state) {
                            $set('box_no', date('d-m-Y', strtotime($state)));
                        }
                    })
                    ->columnSpan(3),

                Forms\Components\TextInput::make('box_no')
                    ->label('বক্স নং')
                    ->readOnly()
                    ->columnSpan(2),
                Forms\Components\TextInput::make('total_amount')
                    ->label('মোট পরিমাণ')
                    ->prefix('Tk. ')
                    ->readOnly()
                    ->reactive()
                    ->columnSpan(2),

                Forms\Components\TextInput::make('discount')
                    ->label('ছাড়')
                    ->suffix(' %')
                    ->numeric()
                    ->default(0)
                    ->readOnly(fn ($record) => in_array($record?->status, ['Paid', 'Released']))
                    ->columnSpan(2),
                Forms\Components\TextInput::make('discount_amount')
                    ->label('ছাড়ের পরিমাণ')
                    ->suffix(' %')
                    ->reactive()
                    ->numeric()
                    ->default(0)
                    ->readOnly(fn ($record) => in_array($record?->status, ['Paid', 'Released']))
                    ->columnSpan(2),
                Forms\Components\TextInput::make('pay_amount')
                    ->label('পরিশোধের পরিমাণ')
                    ->prefix('Tk. ')
                    ->reactive()
                    ->numeric()
                    ->default(0)
                    ->readOnly(fn ($record) => in_array($record?->status, ['Paid', 'Released']))
                    ->columnSpan(2),
                Forms\Components\Select::make('status')
                    ->label('মন্তব্য')
                ->options([
                    'Unpaid'=>'Unpaid',
                    'Paid'=>'Paid',
                    'Released'=>'Released',
                ])
                ->disabled(fn ($record) => in_array($record?->status, ['Paid', 'Released']))
                ->columnSpan(3),
                Section::make('মামলার বিস্তারিত')
                    ->schema([
                        Repeater::make('sections')
                            ->label('অনিয়মের বিবরণ')
                            ->addActionLabel('নতুন অনিয়ম যোগ করুন')
                            ->relationship('lawsuitSections')
                            ->schema([
                               Select::make('section_id')
                                ->label('অনিয়মের ধরণ')
                                ->relationship('section', 'title') // <-- should match the relationship in LawsuitSection model
                                ->required()
                                ->columnSpan(8)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set,callable $get) {
                                    // Find the selected section and set the amount
                                    $section = \App\Models\Section::find($state);
                                    $set('amount', $section?->amount ?? null);

                                     // Recalculate total_amount
                                    $sections = $get('../../sections'); // get all repeater items
                                    $total = collect($sections)->sum(function ($item) use ($state, $section) {
                                        // If this is the current row, use the new amount
                                        if (($item['section_id'] ?? null) == $state) {
                                            return $section?->amount ?? 0;
                                        }
                                        return floatval($item['amount'] ?? 0);
                                    });
                                    $set('../../total_amount', $total);
                                }),
                                TextInput::make('amount')
                                    ->label('জরিমানা')
                                    ->prefix('Tk. ')
                                    ->required()
                                    ->readOnly()
                                    ->maxLength(255)
                                    ->columnSpan(4),
                            ])
                            ->columns(12)
                            ->defaultItems(1)
                            ->minItems(1)
                            ->maxItems(5)
                            ->columnSpan(8)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // $state is the array of all repeater items
                                $total = collect($state)->sum(function ($item) {
                                    return floatval($item['amount'] ?? 0);
                                });
                                $set('total_amount', $total);
                            }),
                            // ->disabled(fn ($record) => in_array($record?->status, ['Paid', 'Dismissed']))

                        Repeater::make('documents')
                            ->label('আটকৃত নথি')
                            ->addActionLabel('নতুন নথি যোগ করুন')
                            ->relationship('lawsuitDocuments')
                            ->schema([
                               Select::make('document_id')
                                    ->label('নথি')
                                    ->relationship('document', 'title') // <-- should match the relationship in LawsuitDocument model
                                    ->required(),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->minItems(1)
                            ->maxItems(5)
                            ->columnSpan(4),
                    ])->columns(12)
                     ->disabled(fn ($record) => in_array($record?->status, ['Paid', 'Released'])),
            ])->columns(12);
    }

    public static function getEloquentQuery(): Builder
    {
        if (auth()->user()->hasRole('tax_collector')) {
            return parent::getEloquentQuery()
                ->where(['case_status' => 'approved', 'status' => 'Unpaid']);
        }
        return parent::getEloquentQuery()
            ->where(['case_status' => 'approved']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vechicle_number')
                    ->label('গাড়ির নম্বর')
                    ->formatStateUsing(function ($state) {
                       return $state ? self::en2bn($state) : '';
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('lawsuit_date')
                    ->label('মামলার তারিখ')
                    ->formatStateUsing(function ($state) {
                       return $state ? self::en2bn($state) : '';
                    }),
                Tables\Columns\TextColumn::make('pay_amount')
                    ->label('মোট পরিমাণ')
                    ->numeric()
                    ->money('BDT', true)
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                       return $state ? self::en2bn($state) : self::en2bn(0);
                    }),
                Tables\Columns\TextColumn::make('box_no')
                    ->label('বক্স নম্বর')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                       return $state ? self::en2bn($state) : '';
                    }),
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('রশিদ নম্বর')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                       return $state ? self::en2bn($state) : '';
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color( fn ($state): string => match ($state) {
                        'Paid' => 'success',
                        'Unpaid' => 'warning',
                        'Released' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(fn ($record) =>
                (auth()->user()->hasRole('tax_collector'))
                    ? static::getUrl('invoice', ['record' => $record])
                    : static::getUrl('edit', ['record' => $record])
            )
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn (Lawsuit $record): bool => $record->status !== 'Released'
                && $record->status !== 'Paid' && !(auth()->user()->hasRole('tax_collector'))),
                Tables\Actions\Action::make('invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (Lawsuit $record): string => route('filament.admin.resources.lawsuits.invoice', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
        $count = Lawsuit::where(['case_status' => 'approved', 'status' => 'Unpaid'])
            ->count();

        return $count > 0 ? (string) $count : null;
    }
    

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLawsuits::route('/'),
            'create' => Pages\CreateLawsuit::route('/create'),
            'edit' => Pages\EditLawsuit::route('/{record}/edit'),
            'invoice' => Pages\CaseInvoice::route('/{record}/invoice'),
        ];
    }
}
