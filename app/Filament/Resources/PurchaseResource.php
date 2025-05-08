<?php
// app/Filament/Resources/PurchaseResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use App\Models\Item;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Pembelian';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pembelian')
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal Pembelian')
                            ->required()
                            ->default(now()),

                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Supplier')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('address')
                                    ->label('Alamat')
                                    ->maxLength(65535),
                                Forms\Components\TextInput::make('phone')
                                    ->label('No. Telepon')
                                    ->maxLength(255),
                                Forms\Components\Toggle::make('active')
                                    ->label('Aktif')
                                    ->default(true),
                            ])
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'ordered' => 'Dipesan',
                                'delivered' => 'Diterima',
                                'completed' => 'Selesai',
                            ])
                            ->default('draft')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('created_by')
                            ->default(function () {
                                return Auth::id();
                            }),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Forms\Components\Section::make('Detail Pembelian')
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('item_id')
                                    ->label('Item/Barang')
                                    ->options(function () {
                                        return Item::where('active', true)
                                            ->get()
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $item = Item::find($state);
                                            if ($item) {
                                                $set('unit_symbol', $item->unit->symbol);
                                            }
                                        }
                                    }),
                                Forms\Components\TextInput::make('unit_symbol')
                                    ->label('Satuan')
                                    ->disabled(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $unitPrice = $get('unit_price') ?? 0;
                                        $quantity = $state ?? 0;
                                        $set('subtotal', $unitPrice * $quantity);
                                    }),
                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $quantity = $get('quantity') ?? 0;
                                        $unitPrice = $state ?? 0;
                                        $set('subtotal', $unitPrice * $quantity);
                                    }),
                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->default(0)
                                    ->dehydrated(true)
                                    ->disabled(),
                                Forms\Components\TextInput::make('received_quantity')
                                    ->label('Jumlah Diterima')
                                    ->numeric()
                                    ->default(0)
                                    ->visible(function (string $context) {
                                        return $context === 'edit';
                                    }),
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'accepted' => 'Diterima',
                                        'rejected' => 'Ditolak',
                                        'partial' => 'Sebagian',
                                    ])
                                    ->default('pending')
                                    ->required()
                                    ->visible(function (string $context) {
                                        return $context === 'edit';
                                    }),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('No. Pembelian')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Harga')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'ordered' => 'warning',
                        'delivered' => 'info',
                        'completed' => 'success',
                    }),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'ordered' => 'Dipesan',
                        'delivered' => 'Diterima',
                        'completed' => 'Selesai',
                    ]),
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('order')
                    ->label('Pesan')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->action(function (Purchase $record) {
                        $record->update(['status' => 'ordered']);

                        Notification::make()
                            ->title('Pesanan berhasil dikirim ke supplier')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Purchase $record) => $record->status === 'draft'),

                Tables\Actions\Action::make('receive')
                    ->label('Terima Barang')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->form([
                        Forms\Components\Repeater::make('details')
                            ->schema([
                                Forms\Components\Hidden::make('id')
                                    ->required(),
                                Forms\Components\TextInput::make('item_name')
                                    ->label('Item')
                                    ->disabled(),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah Pesan')
                                    ->disabled(),
                                Forms\Components\TextInput::make('received_quantity')
                                    ->label('Jumlah Diterima')
                                    ->numeric()
                                    ->required(),
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'accepted' => 'Diterima',
                                        'rejected' => 'Ditolak',
                                        'partial' => 'Sebagian',
                                    ])
                                    ->required(),
                            ])
                            ->columns(3)
                    ])
                    ->action(function (Purchase $record, array $data) {
                        $record->update(['status' => 'delivered']);

                        foreach ($data['details'] as $detail) {
                            $purchaseDetail = \App\Models\PurchaseDetail::find($detail['id']);
                            if ($purchaseDetail) {
                                $purchaseDetail->update([
                                    'received_quantity' => $detail['received_quantity'],
                                    'status' => $detail['status'],
                                ]);

                                // Jika status diterima atau sebagian, update stok
                                if (in_array($detail['status'], ['accepted', 'partial']) && $detail['received_quantity'] > 0) {
                                    // Logika penambahan stok
                                    \App\Models\Stock::create([
                                        'item_id' => $purchaseDetail->item_id,
                                        'quantity' => $detail['received_quantity'],
                                        'batch_number' => 'PO-' . $record->id . '-' . date('Ymd'),
                                        'expiry_date' => null, // Sesuaikan dengan kebutuhan
                                        'location' => 'Gudang', // Default location
                                    ]);
                                }
                            }
                        }

                        Notification::make()
                            ->title('Penerimaan barang berhasil diproses')
                            ->success()
                            ->send();
                    })
                    ->modalSubmitAction(
                        fn(Purchase $record) => $record->status === 'ordered'
                            ? 'Proses Penerimaan'
                            : null
                    )
                    // ->loadingIndicator()
                    ->mountUsing(function (Forms\ComponentContainer $form, Purchase $record) {
                        $details = $record->details->map(function ($detail) {
                            return [
                                'id' => $detail->id,
                                'item_name' => $detail->item->name,
                                'quantity' => $detail->quantity,
                                'received_quantity' => $detail->quantity,
                                'status' => 'accepted',
                            ];
                        })->toArray();

                        $form->fill(['details' => $details]);
                    })
                    ->visible(fn(Purchase $record) => $record->status === 'ordered'),

                Tables\Actions\Action::make('complete')
                    ->label('Selesaikan')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (Purchase $record) {
                        $record->update(['status' => 'completed']);

                        Notification::make()
                            ->title('Pembelian berhasil diselesaikan')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Purchase $record) => $record->status === 'delivered'),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // public static function getRelations(): array
    // {
    //     return [
    //         RelationManagers\DetailsRelationManager::class,
    //         RelationManagers\ReturnsRelationManager::class,
    //     ];
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            // 'view' => Pages\ViewPurchase::route('/{record}'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}
