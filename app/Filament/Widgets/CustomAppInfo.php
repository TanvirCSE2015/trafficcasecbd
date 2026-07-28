<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class CustomAppInfo extends Widget
{
    protected static bool $isLazy = false;
    protected static ?int $sort = -2;
    protected static string $view = 'filament.widgets.custom-app-info';
}
