<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $title = 'ব্যবহারকারী';
    protected static ?string $navigationLabel = 'সফটওয়ার ব্যবহারকারী';

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
                Forms\Components\TextInput::make('name')
                    ->label('নাম')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('ইমেইল')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label('মোবাইল')
                    ->required()
                    ->formatStateUsing(fn ($state) =>self::en2bn( $state))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('office_id')
                    ->label('অফিস')
                    ->relationship('office', 'name')
                    ->required()
                    ->default(null),
                Forms\Components\Select::make('department_id')
                    ->label('শাখা')
                    ->relationship('department', 'name')
                    ->required()
                    ->default(null),
                Forms\Components\Select::make('designation_id')
                    ->label('পদবী')
                    ->relationship('designation', 'name')
                    ->required()
                    ->default(null),
                Forms\Components\Select::make('roles')
                    ->label('ব্যবহারকারীর ভূমিকা')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->multiple()
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->label('পাসওয়ার্ড')
                    ->password()
                    ->required()
                    ->maxLength(255),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('নাম')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('ইমেইল')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('মোবাইল')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => self::en2bn($state)),
                Tables\Columns\TextColumn::make('office.name')
                    ->label('অফিস')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('শাখা')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('designation.name')
                    ->label('পদবী')
                    ->numeric()
                    ->sortable(),
            ])
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
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
