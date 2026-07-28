<?php

namespace App\Filament\Resources\LawsuitResource\Pages;

use App\Filament\Resources\LawsuitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLawsuits extends ListRecords
{
    protected static string $resource = LawsuitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('নতুন মামলা যোগ করুন')
            ->icon('heroicon-o-plus')
            ->color('primary'),
        ];
    }
    public function getTitle(): string
    {
        return 'মামলার তালিকা';
    }
}
