<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Models\CustomerActivityLog;
use App\Models\User;
use App\Services\CustomerAccountService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        $service = app(CustomerAccountService::class);
        $admin   = fn () => Auth::user();

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => \App\Filament\Resources\Customers\CustomerResource::getUrl('view', ['record' => $record])),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                BadgeColumn::make('status')
                    ->colors([
                        'success' => User::STATUS_ACTIVE,
                        'danger'  => User::STATUS_BLOCKED,
                        'warning' => User::STATUS_SUSPENDED,
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst((string) $state))
                    ->sortable(),

                IconColumn::make('has_product_access')
                    ->label('Access')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('last_login_at')
                    ->label('Last Login')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),

                TextColumn::make('last_login_ip')
                    ->label('Last IP')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->options(User::allStatuses()),

                SelectFilter::make('has_product_access')
                    ->label('Product Access')
                    ->options([
                        '1' => 'Has Access',
                        '0' => 'No Access',
                    ]),
            ])

            ->recordActions([
                ViewAction::make(),

                // ── Block ──────────────────────────────────────────────────
                Action::make('block')
                    ->label('Block')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (User $record) => $record->status !== User::STATUS_BLOCKED)
                    ->modalHeading('Block Customer Account')
                    ->modalSubmitActionLabel('Block Account')
                    ->form([
                        Textarea::make('reason')
                            ->label('Block Reason')
                            ->placeholder('Explain why this account is being blocked…')
                            ->required()
                            ->maxLength(500)
                            ->rows(3),
                    ])
                    ->action(function (User $record, array $data) use ($service, $admin) {
                        $service->block($record, $data['reason'], $admin());
                        Notification::make()
                            ->title("Account blocked: {$record->email}")
                            ->danger()
                            ->send();
                    }),

                // ── Unblock ────────────────────────────────────────────────
                Action::make('unblock')
                    ->label('Unblock')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record) => $record->status === User::STATUS_BLOCKED)
                    ->requiresConfirmation()
                    ->modalHeading('Unblock Customer Account')
                    ->modalDescription('This will restore the account to Active status and allow the customer to log in again.')
                    ->action(function (User $record) use ($service, $admin) {
                        $service->unblock($record, $admin());
                        Notification::make()
                            ->title("Account unblocked: {$record->email}")
                            ->success()
                            ->send();
                    }),

                // ── Suspend ────────────────────────────────────────────────
                Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->visible(fn (User $record) => $record->status !== User::STATUS_SUSPENDED && $record->status !== User::STATUS_BLOCKED)
                    ->modalHeading('Suspend Customer Account')
                    ->modalSubmitActionLabel('Suspend Account')
                    ->form([
                        Textarea::make('reason')
                            ->label('Suspension Reason')
                            ->required()
                            ->maxLength(500)
                            ->rows(3),

                        DateTimePicker::make('suspended_until')
                            ->label('Suspend Until')
                            ->helperText('Leave empty for indefinite suspension')
                            ->minDate(now()->addMinutes(5))
                            ->nullable(),
                    ])
                    ->action(function (User $record, array $data) use ($service, $admin) {
                        $until = ! empty($data['suspended_until'])
                            ? Carbon::parse($data['suspended_until'])
                            : null;

                        $service->suspend($record, $data['reason'], $until, $admin());

                        Notification::make()
                            ->title("Account suspended: {$record->email}")
                            ->warning()
                            ->send();
                    }),

                // ── Lift Suspension ────────────────────────────────────────
                Action::make('unsuspend')
                    ->label('Lift Suspension')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(fn (User $record) => $record->status === User::STATUS_SUSPENDED)
                    ->requiresConfirmation()
                    ->modalHeading('Lift Account Suspension')
                    ->action(function (User $record) use ($service, $admin) {
                        $service->unsuspend($record, $admin());
                        Notification::make()
                            ->title("Suspension lifted: {$record->email}")
                            ->success()
                            ->send();
                    }),

                // ── Revoke Product Access ──────────────────────────────────
                Action::make('revoke_access')
                    ->label('Revoke Access')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->visible(fn (User $record) => (bool) $record->has_product_access)
                    ->requiresConfirmation()
                    ->modalHeading('Revoke Product Access')
                    ->modalDescription('The customer will be immediately logged out and can no longer access the product catalogue.')
                    ->action(function (User $record) use ($service, $admin) {
                        $service->revokeAccess($record, $admin());
                        Notification::make()
                            ->title("Product access revoked: {$record->email}")
                            ->danger()
                            ->send();
                    }),

                // ── Grant Product Access ───────────────────────────────────
                Action::make('grant_access')
                    ->label('Grant Access')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->visible(fn (User $record) => ! $record->has_product_access)
                    ->requiresConfirmation()
                    ->modalHeading('Grant Product Access')
                    ->action(function (User $record) use ($service, $admin) {
                        $service->grantAccess($record, $admin());
                        Notification::make()
                            ->title("Product access granted: {$record->email}")
                            ->success()
                            ->send();
                    }),

                // ── Force Logout ───────────────────────────────────────────
                Action::make('force_logout')
                    ->label('Force Logout')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Force Logout All Sessions')
                    ->modalDescription('All active sessions and remember-me tokens for this customer will be invalidated immediately.')
                    ->action(function (User $record) use ($service, $admin) {
                        $service->forceLogout($record, $admin());
                        Notification::make()
                            ->title("All sessions terminated: {$record->email}")
                            ->success()
                            ->send();
                    }),

                // ── Send Password Reset ────────────────────────────────────
                Action::make('send_reset')
                    ->label('Send Password Reset')
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Send Password Reset Email')
                    ->modalDescription('A password reset link will be emailed to this customer.')
                    ->action(function (User $record) use ($service, $admin) {
                        $service->sendPasswordResetEmail($record, $admin());
                        Notification::make()
                            ->title("Password reset email sent to: {$record->email}")
                            ->success()
                            ->send();
                    }),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalDescription('Deleting customers is permanent and removes all their data. Consider blocking instead.'),
                ]),
            ]);
    }
}