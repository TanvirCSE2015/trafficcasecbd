<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('নতুন ব্যবহারকারী')
            ->icon('heroicon-o-plus'),
        ];
    }

    public function getTitle(): string
    {
        return 'ব্যবহারকারীদের তালিকা';
    }
}
