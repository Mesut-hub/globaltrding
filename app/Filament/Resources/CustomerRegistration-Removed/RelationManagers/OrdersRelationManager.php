<?php

namespace App\Filament\Resources\CustomerRegistration\RelationManagers;

use App\Enums\OrderStatusStage;
use App\Mail\OrderConfirmedMail;
use App\Mail\OrderStatusUpdateMail;
use App\Models\CustomerContact;
use App\Models\Order;
use App\Models\OrderStatusUpdate;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)->schema([
                TextInput::make('order_number')->label('Order number (invoice #)')->required(),
                DatePicker::make('order_date')->label('Order date'),
                DatePicker::make('delivered_date')->label('Delivered date'),
                DatePicker::make('balanced_finished_date')->label('Balanced & finished date'),
                TextInput::make('product_name')->label('Product'),
                TextInput::make('payment_term')->label('Payment term'),
                TextInput::make('quantity')->label('Quantity')->numeric(),
                Select::make('quantity_unit')->label('Unit')->options(Order::quantityUnitOptions()),
                Toggle::make('is_contracted')->label('Contracted')->inline(false),
                TextInput::make('delivery_time')->label('Delivery time'),
                TextInput::make('shipping_term')->label('Shipping term'),
                Select::make('delivery_point')
                    ->label('Delivery point')
                    ->options(Order::deliveryPointOptions())
                    ->live()
                    ->required(),
            ]),
            TextInput::make('delivery_address')
                ->label('Delivery address')
                ->visible(fn ($get) => $get('delivery_point') === 'other')
                ->required(fn ($get) => $get('delivery_point') === 'other')
                ->columnSpanFull(),
            Textarea::make('description')->rows(2)->columnSpanFull(),
            CheckboxList::make('responsiblePersons')
                ->label('Responsible person(s)')
                ->relationship('responsiblePersons', 'name')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->label('Order')->fontFamily('mono'),
                TextColumn::make('product_name')->label('Product'),
                TextColumn::make('quantity')->label('Qty')->formatStateUsing(fn ($record) => rtrim(rtrim((string) $record->quantity, '0'), '.') . ' ' . $record->quantity_unit),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === Order::STATUS_DRAFT ? '⚠ Draft' : 'Sent')
                    ->color(fn (string $state) => $state === Order::STATUS_DRAFT ? 'warning' : 'success'),
                TextColumn::make('order_date')->date(),
            ])
            ->headerActions([
                CreateAction::make()->label('Add order')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['status'] = Order::STATUS_DRAFT;
                        $data['created_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('sendOrder')
                    ->label('Save & send')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (Order $record) => $record->status === Order::STATUS_DRAFT)
                    ->requiresConfirmation()
                    ->modalDescription('Emails the order confirmation to every checked responsible person and marks this order visible in the customer\'s portal.')
                    ->action(function (Order $record) {
                        $recipients = $record->responsiblePersons()->where('receives_notifications', true)->whereNotNull('email')->get();

                        foreach ($recipients as $contact) {
                            Mail::to($contact->email)->send(new OrderConfirmedMail($record, $contact));
                        }

                        $record->update(['status' => Order::STATUS_SENT, 'sent_at' => now()]);
                        Notification::make()->title('Order sent')->success()->send();
                    }),

                Action::make('cargoStatus')
                    ->label('Cargo status')
                    ->icon('heroicon-o-truck')
                    ->visible(fn (Order $record) => $record->status === Order::STATUS_SENT)
                    ->modalHeading(fn (Order $record) => "Cargo status — {$record->order_number}")
                    ->modalContent(fn (Order $record) => view('filament.orders.status-history', ['order' => $record]))
                    ->schema([
                        Select::make('stage_key')
                            ->label('Stage')
                            ->options(OrderStatusStage::options() + ['custom' => 'Custom stage…'])
                            ->live()
                            ->required(),
                        TextInput::make('custom_label')
                            ->label('Custom stage name')
                            ->visible(fn ($get) => $get('stage_key') === 'custom')
                            ->required(fn ($get) => $get('stage_key') === 'custom'),
                        DatePicker::make('stage_date')->label('Date')->default(now())->required(),
                        Textarea::make('notes')->label('Notes (optional)')->rows(2),
                        Toggle::make('send_email')->label('Also email this update to responsible person(s)')->default(true),
                    ])
                    ->action(function (Order $record, array $data) {
                        $stageKey = $data['stage_key'];
                        $label = $stageKey === 'custom'
                            ? $data['custom_label']
                            : OrderStatusStage::from($stageKey)->label();

                        $update = OrderStatusUpdate::create([
                            'order_id' => $record->id,
                            'stage_key' => $stageKey === 'custom' ? \Illuminate\Support\Str::slug($label, '_') : $stageKey,
                            'stage_label' => $label,
                            'stage_date' => $data['stage_date'],
                            'notes' => $data['notes'] ?? null,
                            'created_by' => auth()->id(),
                        ]);

                        if (! empty($data['send_email'])) {
                            $recipients = $record->responsiblePersons()->where('receives_notifications', true)->whereNotNull('email')->get();
                            foreach ($recipients as $contact) {
                                Mail::to($contact->email)->send(new OrderStatusUpdateMail($record, $update, $contact));
                            }
                            $record->update(['status_last_sent_at' => now()]);
                        }

                        Notification::make()->title('Cargo status updated')->success()->send();
                    }),
            ]);
    }
}