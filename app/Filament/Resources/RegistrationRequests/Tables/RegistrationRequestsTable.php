<?php

namespace App\Filament\Resources\RegistrationRequests\Tables;

use App\Filament\Resources\RegistrationRequests\RegistrationRequestResource;
use App\Mail\ProductAccessApprovedMail;
use App\Models\RegistrationRequest;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class RegistrationRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')
                    ->searchable()
                    ->url(fn ($record) => RegistrationRequestResource::getUrl('view', ['record' => $record])),

                TextColumn::make('first_name')->searchable(),
                TextColumn::make('last_name')->searchable(),
                TextColumn::make('company')->searchable(),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'submitted',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst((string)$state))
                    ->sortable(),

                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('reviewed_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'submitted' => 'Submitted',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ]),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->visible(fn (RegistrationRequest $r) => $r->status === 'submitted')
                    ->requiresConfirmation()
                    ->action(function (RegistrationRequest $r) {
                        // Create or update user
                        $user = User::query()->where('email', $r->email)->first();

                        if (!$user) {
                            // Create with random password; user will set via reset link anyway.
                            $random = bin2hex(random_bytes(16));
                            $user = User::create([
                                'name' => trim($r->first_name . ' ' . $r->last_name),
                                'email' => $r->email,
                                'password' => Hash::make($random),
                                'has_product_access' => true,
                            ]);
                        } else {
                            $user->update(['has_product_access' => true]);
                        }

                        // Create password reset token and email link
                        $token = Password::createToken($user);

                        Mail::to($user->email)->send(new ProductAccessApprovedMail($user, $token));

                        $r->update([
                            'status' => 'approved',
                            'reviewed_at' => now(),
                            'reviewed_by' => Auth::id(),
                        ]);

                        Notification::make()->title('Approved. Reset link email sent.')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->visible(fn (RegistrationRequest $r) => $r->status === 'submitted')
                    ->requiresConfirmation()
                    ->action(function (RegistrationRequest $r) {
                        $r->update([
                            'status' => 'rejected',
                            'reviewed_at' => now(),
                            'reviewed_by' => Auth::id(),
                        ]);

                        Notification::make()->title('Rejected.')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}