<?php

namespace App\Filament\Resources\DesignationResource\Pages;

use App\Filament\Resources\DesignationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDesignations extends ListRecords
{
    protected static string $resource = DesignationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('নতুন পদবী')
            ->icon('heroicon-o-plus'),
        ];
    }
    public function getTitle(): string
    {
        return 'পদবীর তালিকা';
    }
}
