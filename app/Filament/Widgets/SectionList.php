<?php

namespace App\Filament\Widgets;

use App\Models\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class SectionList extends BaseWidget
{
    protected static ?string $title = 'অনিয়মের তালিকা';
    protected static ?string $heading = 'অনিয়মের তালিকা';
    protected static ?int $sort = 3;
    protected static bool $isLazy = false;
    protected int | string | array $columnSpan = 'full';
    
    public static function canView(): bool
    {
        return auth()->user()?->can('widget_SectionList');
    }


    public function table(Table $table): Table
    {
        return $table
        ->query(
            Section::query()
        )
        ->columns([
            Tables\Columns\TextColumn::make('section_no')
                ->label('ক্রমিক নং')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('title')
                ->label('শিরোনাম')
                ->searchable()
                ->sortable(),
        ]);
    }
}
