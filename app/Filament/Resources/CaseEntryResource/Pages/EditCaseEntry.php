<?php

namespace App\Filament\Resources\CaseEntryResource\Pages;

use App\Filament\Resources\CaseEntryResource;
use App\Models\LawsuitDocument;
use App\Models\LawsuitSection;
use App\Models\Section;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCaseEntry extends EditRecord
{
    protected static string $resource = CaseEntryResource::class;

    protected array $selectedSections = [];
    protected array $selectedDocuments = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
            ->label('মুছে ফেলুন'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!isset($data['total_amount']) || $data['total_amount'] === null) {
            $data['total_amount'] = \App\Models\Section::whereIn('id', $data['lawsuitSections'] ?? [])->sum('amount');
            $data['discount_amount'] = $data['total_amount'] * ($data['discount'] ?? 0) / 100;
            $data['pay_amount'] = $data['total_amount'] - $data['discount_amount'];
            $data['mp_amount'] = $data['pay_amount'] * ($data['mp_percentage'] ?? 25) / 100;
            $data['board_amount'] = $data['pay_amount'] - $data['mp_amount'];
        }

        // Set box_no if not set
        if (!isset($data['box_no']) || $data['box_no'] === null) {
            $data['box_no'] = $data['lawsuit_date'] ? date('d-m-Y', strtotime($data['lawsuit_date'])) : null;
        }
        $this->selectedSections = $data['lawsuitSections'] ?? [];
        $this->selectedDocuments = $data['lawsuitDocuments'] ?? [];
        unset($data['lawsuitSections'], $data['lawsuitDocuments']);
        return $data;
    }

    protected function afterSave(): void
    {
        // Remove old sections
        LawsuitSection::where('lawsuit_id', $this->record->id)->delete();
        LawsuitDocument::where('lawsuit_id', $this->record->id)->delete();

        // Insert new ones
        foreach ($this->selectedSections as $sectionId) {
            $section = Section::find($sectionId);
            LawsuitSection::create([
                'lawsuit_id' => $this->record->id,
                'section_id' => $sectionId,
                'amount' => $section?->amount ?? 0,
            ]);
        }

        // Insert new documents
        foreach ($this->selectedDocuments as $documentId) {
            LawsuitDocument::create([
                'lawsuit_id' => $this->record->id,
                'document_id' => $documentId,
            ]);
        }
    }

     protected function mutateFormDataBeforeFill(array $data): array
    {
        // Pre-fill sections
        $data['lawsuitSections'] = LawsuitSection::where('lawsuit_id', $this->record->id)
            ->pluck('section_id')
            ->toArray();

        // Pre-fill documents
        $data['lawsuitDocuments'] = LawsuitDocument::where('lawsuit_id', $this->record->id)
            ->pluck('document_id')
            ->toArray();

        return $data;
    }


    protected function getFormActions(): array
    {

        return [
            $this->getSaveFormAction()
            ->label('হালনাগাদ করুন'),
            $this->getCancelFormAction()
            ->label('ফিরে যান'),
        ];//parent::getFormActions();
    }

    public function getTitle(): string
    {
        return 'হালনাগাদ করুন';
    }
    
    public function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected function getSavedNotificationTitle(): ?string
    {
        return 'মামলাটি সফলভাবে হালনাগাদ করা হয়েছে';
    }
}
