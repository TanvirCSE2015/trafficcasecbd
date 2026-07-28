<?php

namespace App\Filament\Resources\LawsuitResource\Pages;

use App\Filament\Resources\LawsuitResource;
use App\Models\CaseInvoice as ModelsCaseInvoice;
use App\Models\Lawsuit;
use App\Models\LawsuitDocument;
use App\Models\LawsuitSection;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Database\Eloquent\Builder;

class CaseInvoice extends Page implements HasTable, HasForms
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $resource = LawsuitResource::class;

    protected static string $view = 'filament.resources.lawsuit-resource.pages.case-invoice';

    public array $formData = [];
    public ?int $record = null;
    public ?Lawsuit $lawsuit=null;
    public array $documents = [];
    public string $documentTitles = '';

    public function mount(): void
    {
        $this->lawsuit = Lawsuit::find($this->record);

        $this->documents = LawsuitDocument::where('lawsuit_id', $this->record)
            ->with('document')
            ->get()
            ->all();

        $this->form->fill([
            'vechicle_number' => $this->lawsuit?->vechicle_number,
            'lawsuit_date' => $this->lawsuit->lawsuit_date,
            'box_no' => $this->lawsuit->box_no,
            'status' => $this->lawsuit->status,
            'discount' => $this->lawsuit->discount,
            'pay_amount' => $this->lawsuit->pay_amount,

        ]);

        foreach ($this->documents as $document) {
            $this->documentTitles .= $document->document->title . ', ';
        }
    }

    public function en2bn($number)
    {
        $en = ['0','1','2','3','4','5','6','7','8','9','January','February','March','April','May','June','July','August','September','October','November','December'];
        $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯','জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
        return str_replace($en, $bn, $number);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('vechicle_number')
                    ->label('গাড়ীর নম্বর')
                    ->readOnly(),
                TextInput::make('lawsuit_date')
                    ->label('মামলার তারিখ')
                    ->readOnly()
                    ->formatStateUsing(fn ($state) => $this->en2bn($state)),
                TextInput::make('box_no')
                    ->label('বক্স নম্বর')
                    ->readOnly()
                    ->formatStateUsing(fn ($state) => $this->en2bn($state)),
                Select::make('status')
                    ->label('মন্তব্য')
                    ->options([
                        'Paid' => 'Paid',
                        'Unpaid' => 'Unpaid',
                        'Released' => 'Released',
                    ])
                    ->disabled(),
                TextInput::make('discount')
                    ->label('ছাড়')
                    ->suffix(' %')
                    ->default(0)
                    ->readOnly()
                    ->formatStateUsing(fn ($state) => $this->en2bn($state)),
                TextInput::make('pay_amount')
                    ->label('মোট টাকা')
                    ->prefix('Tk. ')
                    ->readOnly()
                    ->formatStateUsing(fn ($state) => $this->en2bn($state)),

            ])
            ->columns(6)
            ->statePath('formData');
    }


    public function getTableQuery(): Builder
    {
        return LawsuitSection::query()->where('lawsuit_id', $this->record);
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('section.title')
                ->label('অনিয়মের ধরণ'),
            TextColumn::make('amount')
                ->label('টাকা')
                ->money('BDT', true)
                ->formatStateUsing(function ($state) {
                    return $this->en2bn($state);
                }),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('রিসিট প্রিন্ট করুন')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(route('invoice.print', ['lawsuit' => $this->record]))
                ->openUrlInNewTab()
                ->visible(fn () => $this->lawsuit && $this->lawsuit->status !== 'Unpaid'),
            Action::make('Payment')
                ->label('পরিশোধ করুন')
                ->requiresConfirmation()
                ->action(function () {
                    $invoice=ModelsCaseInvoice::create([
                        'lawsuit_id' => $this->record,
                        'car_no' => $this->lawsuit->vechicle_number,
                        'invoice_date' => date('d-m-Y'),
                        'month' => date('m'),
                        'month_name' => now()->format('F'),
                        'year' => now()->year,
                        'total_amount' => $this->lawsuit->total_amount,
                        'discount' => $this->lawsuit->discount,
                        'discount_amount' => $this->lawsuit->discount_amount,
                        'pay_amount' => $this->lawsuit->pay_amount,
                        'mp_percentage' => $this->lawsuit->mp_percentage,
                        'mp_amount' => $this->lawsuit->mp_amount,
                        'board_amount' => $this->lawsuit->board_amount,
                        'office_id' => $this->lawsuit->office_id,
                        'created_by' => auth()->id(),
                        'status' => 'Paid',
                    ]);

                    $this->lawsuit->update([
                        'status' => 'Paid',
                        'pay_date' => date('d-m-Y'),
                        'p_month' => date('m'),
                        'p_year' => date('Y'),
                        'p_month_name' => date('F'),
                        'paid_user_id' => auth()->id(),
                        'invoice_no' => $invoice->invoice_number,
                    ]);
                    Notification::make()
                    ->title('Payment status updated to Paid.')
                    ->success()
                    ->send();
                    $this->redirect(request()->header('Referer'));
                })
                // ->icon('heroicon-o-download')
                ->color('success')
                // ->url(route('lawsuit.invoice.download', ['lawsuit' => $this->record]))
                ->visible(fn () => $this->lawsuit && $this->lawsuit->status === 'Unpaid')
                ->openUrlInNewTab(),
        ];
    }

    public function getTitle(): string
    {
        return 'মামলা নিষ্পত্তি';
    }
}
