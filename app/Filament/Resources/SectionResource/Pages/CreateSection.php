<?php

namespace App\Filament\Resources\SectionResource\Pages;

use App\Filament\Resources\SectionResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSection extends CreateRecord
{
    protected static string $resource = SectionResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return Notification::make()
            ->title('Section created successfully!')
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
        return 'নতুন ধারা';
    }
}
