<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('মুছে ফেলুন'),
        ];
    }

    public function getTitle(): string
    {
        return 'হালনাগাদ করুন';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('সংরক্ষণ করুন'),
            $this->getCancelFormAction()
                ->label('ফিরে যান'),
        ];
    }
}
