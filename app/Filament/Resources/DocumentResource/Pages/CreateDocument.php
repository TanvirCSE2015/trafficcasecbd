<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return Notification::make()
            ->title('Document created successfully!')
            ->success();
    }
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('সংরক্ষণ করুন'),
            $this->getCreateAnotherFormAction()
                ->label('সংরক্ষণ এবং নতুন যোগ করুন'),
            $this->getCancelFormAction()
                ->label('ফিরে যান'),
        ];
    }
    public function getTitle(): string
    {
        return 'নতুন নথি';
    }
}
