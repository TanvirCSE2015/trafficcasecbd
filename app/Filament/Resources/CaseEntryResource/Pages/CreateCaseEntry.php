<?php

namespace App\Filament\Resources\CaseEntryResource\Pages;

use App\Filament\Resources\CaseEntryResource;
use App\Models\LawsuitDocument;
use App\Models\LawsuitSection;
use App\Models\Section;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCaseEntry extends CreateRecord
{
    protected static string $resource = CaseEntryResource::class;

    /**
     * @var array
     */
    protected array $selectedSections = [];

    /**
     * @var array
     */
    protected array $selectedDocuments = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!isset($data['total_amount']) || $data['total_amount'] === null) {
            $data['total_amount'] = \App\Models\Section::whereIn('id', $data['lawsuitSections'] ?? [])->sum('amount');
            $data['discount_amount'] = $data['total_amount'] * ($data['discount'] ?? 0) / 100;
            $data['pay_amount'] = $data['total_amount'] - $data['discount_amount'];
            $data['mp_amount'] = $data['pay_amount'] * ($data['mp_percentage'] ?? 25) / 100;
            $data['board_amount'] = $data['pay_amount'] - $data['mp_amount']; // Assuming board amount is 10% of mp
        }

        // Set box_no if not set
        if (!isset($data['box_no']) || $data['box_no'] === null) {
            $data['box_no'] = $data['lawsuit_date'] ? date('d-m-Y', strtotime($data['lawsuit_date'])) : null;
        }
        // Store selected sections and documents for use after create
        $this->selectedSections = $data['lawsuitSections'] ?? [];
        $this->selectedDocuments = $data['lawsuitDocuments'] ?? [];
        unset($data['lawsuitSections'], $data['lawsuitDocuments']);
        return $data;
    }

    protected function afterCreate(): void
    {
        // Insert into lawsuit_sections
        foreach ($this->selectedSections as $sectionId) {
            $section = Section::find($sectionId);
            LawsuitSection::create([
                'lawsuit_id' => $this->record->id,
                'section_id' => $sectionId,
                'amount' => $section?->amount ?? 0,
            ]);
        }

        // Insert into lawsuit_documents
        foreach ($this->selectedDocuments as $documentId) {
            LawsuitDocument::create([
                'lawsuit_id' => $this->record->id,
                'document_id' => $documentId,
            ]);
        }
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
            ->label('সংরক্ষণ করুন'),
            $this->getCreateAnotherFormAction()
            ->label('সংরক্ষণ এবং নতুন মামলা যোগ করুন'),
            $this->getCancelFormAction()
            ->label('বাতিল করুন'),
        ];//parent::getFormActions();
    }

    public function getTitle(): string
    {
        return 'নতুন মামলা';
    }
    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return Notification::make()
            ->title('নতুন মামলা সফলভাবে সংরক্ষণ করা হয়েছে')
            ->success();
    }
}
