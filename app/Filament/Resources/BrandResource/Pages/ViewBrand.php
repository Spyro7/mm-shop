<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBrand extends ViewRecord
{
    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('Cancel')
                ->color('gray')
                ->url('/admin/brands'),
        ];
    }
    protected static function getFooterActions(): array
    {
        return [
            Action::make('cancel')
                ->label('Cancel')
                ->url('/admin/dashboard')
                ->color('secondary')
                ->icon('heroicon-o-x'),
        ];
    }
}
