<?php

namespace App\Filament\Resources\OfficeResource\Pages;

use App\Filament\Resources\OfficeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOffice extends CreateRecord
{
    protected static string $resource = OfficeResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->title('Office created successfully!')
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
        return 'নতুন অফিস';
    }
}
