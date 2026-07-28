<?php

namespace App\Filament\Resources\SectionResource\Pages;

use App\Filament\Resources\SectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSection extends EditRecord
{
    protected static string $resource = SectionResource::class;

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
        return 'Section updated successfully!';
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
