<?php

namespace App\Filament\Resources\VehicleCategoryResource\Pages;

use App\Filament\Resources\VehicleCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVehicleCategory extends EditRecord
{
    protected static string $resource = VehicleCategoryResource::class;

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
        return 'গাড়ীর ধরণ সফলভাবে হালনাগাদ হয়েছে।';
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
