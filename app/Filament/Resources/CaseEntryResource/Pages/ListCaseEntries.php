<?php

namespace App\Filament\Resources\CaseEntryResource\Pages;

use App\Filament\Resources\CaseEntryResource;
use Filament\Actions;
use Filament\Actions\Modal\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCaseEntries extends ListRecords
{
    protected static string $resource = CaseEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('নতুন মামলা যোগ করুন')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ,
            Actions\Action::make('Approve')
                ->label('মামলা প্রেরণ করুন')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('নিশ্চিতকরণ প্রয়োজন')
                ->modalDescription('আপনি কি নিশ্চিত যে আপনি এই মামলাটি প্রেরণ করতে চান?')
                ->modalSubmitActionLabel('হ্যাঁ, প্রেরণ করুন')
                ->modalCancelActionLabel('না, বাতিল করুন')
                ->action(function () {
                     \App\Models\Lawsuit::where(['office_id'=> auth()->user()->office_id, 'case_status'=> 'pending'])
                    ->update(['case_status' => 'approved','approval_date' => now()->format('d-m-Y')]);

                     Notification::make()
                        ->title('মামলা সফলভাবে প্রেরণ করা হয়েছে।')
                        ->success()
                        ->send();
                     $this->dispatch('refreshList');
                })
                ->visible(fn () => auth()->user()->hasRole('mp_admin')),

        ];
    }

    public function getTitle(): string
    {
        return 'মামলার তালিকা';
    }
}
