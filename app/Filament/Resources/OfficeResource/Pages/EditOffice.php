<?php

namespace App\Filament\Resources\OfficeResource\Pages;

use App\Filament\Resources\OfficeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOffice extends EditRecord
{
    protected static string $resource = OfficeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
            ->label('মুছে ফেলুন'),
        ];
    }

    public function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Office updated successfully!';
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

    public function getTitle(): string
    {
        return 'হালনাগাদ করুন ';
    }
}
