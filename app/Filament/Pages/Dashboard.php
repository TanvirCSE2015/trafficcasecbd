<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\SectionList;
use Filament\Pages\Page;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboard';

    protected static ?string $navigationLabel='ড্যাশবোর্ড';

    public function getTitle(): string | Htmlable
    {
        return 'ড্যাশবোর্ড';
    }

}
