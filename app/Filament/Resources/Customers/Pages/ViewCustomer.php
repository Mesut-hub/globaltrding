<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\User;
use App\Services\CustomerAccountService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                Section::make('Account Details')->schema([
                    TextInput::make('name')->disabled(),
                    TextInput::make('email')->disabled(),
                    TextInput::make('status')
                        ->disabled()
                        ->formatStateUsing(fn ($state) => ucfirst((string) $state)),
                    TextInput::make('has_product_access')
                        ->label('Product Access')
                        ->disabled()
                        ->formatStateUsing(fn ($state) => $state ? 'Granted' : 'Not Granted'),
                ]),

                Section::make('Login & Security')->schema([
                    Placeholder::make('last_login_at')
                        ->label('Last Login')
                        ->content(fn ($record) => $record?->last_login_at?->format('d M Y H:i') ?? 'Never'),

                    TextInput::make('last_login_ip')
                        ->label('Last Login IP')
                        ->disabled(),

                    Placeholder::make('created_at')
                        ->label('Registered At')
                        ->content(fn ($record) => $record?->created_at?->format('d M Y H:i')),

                    Placeholder::make('email_verified_at')
                        ->label('Email Verified')
                        ->content(fn ($record) => $record?->email_verified_at?->format('d M Y H:i') ?? 'Not verified'),
                ]),
            ]),

            Section::make('Restriction Details')
                ->collapsed()
                ->schema([
                    Placeholder::make('blocked_at')
                        ->label('Blocked At')
                        ->content(fn ($record) => $record?->blocked_at?->format('d M Y H:i') ?? '—'),

                    TextInput::make('blocked_reason')
                        ->label('Block Reason')
                        ->disabled()
                        ->placeholder('—'),

                    Placeholder::make('suspended_until')
                        ->label('Suspended Until')
                        ->content(fn ($record) => $record?->suspended_until?->format('d M Y H:i') ?? '—'),

                    TextInput::make('suspended_reason')
                        ->label('Suspension Reason')
                        ->disabled()
                        ->placeholder('—'),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        $service = app(CustomerAccountService::class);
        $admin   = fn () => Auth::user();

        return [
            Action::make('block')
                ->label('Block Account')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->visible(fn () => $this->record->status !== User::STATUS_BLOCKED)
                ->modalHeading('Block Customer Account')
                ->form([
                    Textarea::make('reason')->label('Reason')->required()->maxLength(500),
                ])
                ->action(function (array $data) use ($service, $admin) {
                    $service->block($this->record, $data['reason'], $admin());
                    $this->refreshFormData(['status', 'blocked_at', 'blocked_reason']);
                    Notification::make()->title('Account blocked')->danger()->send();
                }),

            Action::make('unblock')
                ->label('Unblock Account')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === User::STATUS_BLOCKED)
                ->requiresConfirmation()
                ->action(function () use ($service, $admin) {
                    $service->unblock($this->record, $admin());
                    $this->refreshFormData(['status', 'blocked_at', 'blocked_reason']);
                    Notification::make()->title('Account unblocked')->success()->send();
                }),

            Action::make('suspend')
                ->label('Suspend')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->visible(fn () => $this->record->status === User::STATUS_ACTIVE)
                ->modalHeading('Suspend Account')
                ->form([
                    Textarea::make('reason')->label('Reason')->required()->maxLength(500),
                    DateTimePicker::make('suspended_until')->label('Until')->nullable(),
                ])
                ->action(function (array $data) use ($service, $admin) {
                    $until = ! empty($data['suspended_until']) ? Carbon::parse($data['suspended_until']) : null;
                    $service->suspend($this->record, $data['reason'], $until, $admin());
                    $this->refreshFormData(['status', 'suspended_until', 'suspended_reason']);
                    Notification::make()->title('Account suspended')->warning()->send();
                }),

            Action::make('force_logout')
                ->label('Force Logout')
                ->icon('heroicon-o-arrow-right-on-rectangle')
                ->color('gray')
                ->requiresConfirmation()
                ->action(function () use ($service, $admin) {
                    $service->forceLogout($this->record, $admin());
                    Notification::make()->title('All sessions terminated')->success()->send();
                }),

            Action::make('send_reset')
                ->label('Send Password Reset')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->requiresConfirmation()
                ->action(function () use ($service, $admin) {
                    $service->sendPasswordResetEmail($this->record, $admin());
                    Notification::make()->title('Password reset email sent')->success()->send();
                }),
        ];
    }
}