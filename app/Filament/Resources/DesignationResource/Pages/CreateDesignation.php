<?php

namespace App\Filament\Resources\DesignationResource\Pages;

use App\Filament\Resources\DesignationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDesignation extends CreateRecord
{
    protected static string $resource = DesignationResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return \Filament\Notifications\Notification::make()
            ->title('Designation created successfully!')
            ->success();
    }

    public function getTitle(): string
    {
        return 'নতুন পদবী';
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
        ];//parent::getFormActions();
    }
}
