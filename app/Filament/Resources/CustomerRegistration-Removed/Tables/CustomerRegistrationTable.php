<?php

namespace App\Filament\Resources\CustomerRegistration\Tables;

use App\Models\Customer;
use App\Services\CustomerCredentialService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerRegistrationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_code')->label('ID')->fontFamily('mono')->searchable(),
                TextColumn::make('company_name')->label('Company')->searchable()->weight('bold'),
                TextColumn::make('email')->label('Email'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        Customer::STATUS_DRAFT => '⚠ Draft',
                        Customer::STATUS_ACTIVE => 'Active',
                        Customer::STATUS_BLOCKED => 'Blocked',
                        Customer::STATUS_SUSPENDED => 'Suspended',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        Customer::STATUS_DRAFT => 'warning',
                        Customer::STATUS_ACTIVE => 'success',
                        Customer::STATUS_BLOCKED, Customer::STATUS_SUSPENDED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('Registered')->date()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('sendRegistration')
                    ->label('Send registration')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (Customer $record) => $record->isDraft())
                    ->requiresConfirmation()
                    ->modalDescription('This emails every notified contact their portal login and the registration confirmation, and marks this customer as Active.')
                    ->action(function (Customer $record) {
                        if ($record->contacts()->where('receives_notifications', true)->whereNotNull('email')->doesntExist()) {
                            Notification::make()->title('No notifiable contact with an email on file')->danger()->send();
                            return;
                        }

                        app(CustomerCredentialService::class)->sendRegistration($record, auth()->id());

                        Notification::make()->title('Registration sent')->success()->send();
                    }),

                Action::make('resetPassword')
                    ->label('Reset password')
                    ->icon('heroicon-o-key')
                    ->visible(fn (Customer $record) => $record->isActive())
                    ->requiresConfirmation()
                    ->modalDescription('Generates a new password and emails it to this company\'s notified contacts — same process as the customer\'s own "Forgot password" flow.')
                    ->action(function (Customer $record) {
                        app(CustomerCredentialService::class)->resetPassword($record, auth()->id());
                        Notification::make()->title('New password sent')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}