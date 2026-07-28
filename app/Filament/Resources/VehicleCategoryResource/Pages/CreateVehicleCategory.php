<?php

namespace App\Filament\Resources\VehicleCategoryResource\Pages;

use App\Filament\Resources\VehicleCategoryResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateVehicleCategory extends CreateRecord
{
    protected static string $resource = VehicleCategoryResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return Notification::make()
            ->title('গাড়ীর ধরণ সফলভাবে তৈরি হয়েছে।')
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
        return 'নতুন গাড়ীর ধরণ যোগ করুন';
    }
}
