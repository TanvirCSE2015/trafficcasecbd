<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepartments extends ListRecords
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('নতুন যোগ করুন')
            ->icon('heroicon-o-plus'),
        ];
    }

    public function getTitle(): string
    {
        return 'শাখাসমূহের তালিকা';
    }
}
