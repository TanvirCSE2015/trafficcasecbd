<?php

namespace App\Filament\Resources\LawsuitResource\Pages;

use App\Filament\Resources\LawsuitResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLawsuit extends CreateRecord
{
    protected static string $resource = LawsuitResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
            ->label('সংরক্ষণ করুন'),
            $this->getCreateAnotherFormAction()
            ->label('সংরক্ষণ এবং নতুন মামলা যোগ করুন'),
            $this->getCancelFormAction()
            ->label('ফিরে যান'),
        ];//parent::getFormActions();
    }

    public function getTitle(): string
    {
        return 'নতুন মামলা';
    }
}
