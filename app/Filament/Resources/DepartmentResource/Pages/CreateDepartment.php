<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return Notification::make()
            ->title('Department created successfully!')
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
        ];//parent::getFormActions();
    }

    public function getTitle(): string
    {
        return 'নতুন শাখা';
    }
}
