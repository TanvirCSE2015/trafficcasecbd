<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

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
        return 'Document updated successfully!';
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
