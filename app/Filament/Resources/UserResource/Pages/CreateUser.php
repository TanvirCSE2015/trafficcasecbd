<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

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
        return 'নতুন ব্যবহারকারী';
    }
}
