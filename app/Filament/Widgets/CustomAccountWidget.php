<?php

namespace App\Filament\Widgets;

use Filament\Widgets\AccountWidget as BaseAccountWidget;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\Widget;

class CustomAccountWidget extends BaseAccountWidget
{
    protected static string $view = 'filament.widgets.custom-account-widget';

    protected static ?int $sort = -2;
    protected static bool $isLazy = false;
    protected function getViewData(): array
    {
        return [
            'user' => Auth::user(),
            'avatarUrl' => asset('images/avatar.png'), // Change to your image
        ];
    }
}
