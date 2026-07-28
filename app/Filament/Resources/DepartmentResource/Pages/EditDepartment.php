<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepartment extends EditRecord
{
    protected static string $resource = DepartmentResource::class;

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
        return 'Department updated successfully!';
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
