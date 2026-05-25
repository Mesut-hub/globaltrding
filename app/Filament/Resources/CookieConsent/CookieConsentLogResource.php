<?php

namespace App\Filament\Resources\CookieConsent;

use App\Filament\Concerns\HasPermission;
use App\Models\CookieConsentLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CookieConsentLogResource extends Resource
{
    use HasPermission;

    protected static string $permissionKey = 'cookie_consent_logs';
    protected static ?string $model = CookieConsentLog::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static \UnitEnum|string|null $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Consent Logs';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('consented_at', 'desc')
            ->columns([
                TextColumn::make('session_id')->limit(20)->label('Session'),
                TextColumn::make('locale')->badge(),
                TextColumn::make('consent_version')->badge()->label('Version'),
                TextColumn::make('consents')
                    ->formatStateUsing(fn ($state) => collect($state ?? [])
                        ->filter(fn ($v) => $v === true)
                        ->keys()
                        ->join(', '))
                    ->label('Accepted Categories'),
                TextColumn::make('consented_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('locale')
                    ->options(array_combine(
                        config('locales.supported', ['en']),
                        array_map('strtoupper', config('locales.supported', ['en']))
                    )),
                SelectFilter::make('consent_version'),
            ]);
    }

    // Read-only resource — no create/edit
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\CookieConsent\Pages\ListCookieConsentLogs::route('/'),
        ];
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
}