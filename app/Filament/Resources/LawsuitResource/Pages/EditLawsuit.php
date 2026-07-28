<?php

namespace App\Filament\Resources\LawsuitResource\Pages;

use App\Filament\Resources\LawsuitResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLawsuit extends EditRecord
{
    protected static string $resource = LawsuitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
            ->label('মুছে ফেলুন')
            ->visible(fn ($record): bool => $record->status === 'Unpaid'),
        ];
    }
    protected function getFormActions(): array
    {
        // If status is approved, only show the Cancel button
        if ($this->record && ($this->record->status !== 'Unpaid')) {
            return [
                $this->getCancelFormAction()
                ->label('ফিরে যান'),
            ];
        }
        // Otherwise, show all default actions

        return [
            $this->getSaveFormAction()
            ->label('সংরক্ষণ করুন'),
            $this->getCancelFormAction()
            ->label('ফিরে যান'),
        ];//parent::getFormActions();
    }

    public function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'মামলা সফলভাবে আপডেট করা হয়েছে!';
    }

    public function getTitle(): string
    {
        return 'হালনাগাদ করুন ';
    }

}
