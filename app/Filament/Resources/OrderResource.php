<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Template;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Mpdf\Mpdf;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $modelLabel = 'Fill Form';
    protected static ?string $navigationGroup = 'Forms';
    protected static ?string $recordTitleAttribute = 'client_name';
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    public static function shouldRegisterNavigation(): bool
    {
        return  Auth::user()->can('Fill Form');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Client Information')->schema([
                    Forms\Components\TextInput::make('client_name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('user_id')
                        ->relationship('user', 'name')
                        ->required()
                        ->default(auth()->user()->id)
                        ->disabled()
                        ->dehydrated()
                        ->label('Filled By'),
                ])->columns(2),
                Forms\Components\Section::make('Form Information')->schema([
                    Forms\Components\Repeater::make('items')->relationship('items')->label('Form')
                        ->schema([
                        Forms\Components\Select::make('template_id')
                            ->relationship('template', 'name')
                            ->reactive()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                            ->afterStateUpdated(fn($state, callable $set) => $set('content', Template::find($state)?->content))
                            ->label('Form'),
                        Builder::make('content')
                            ->blocks([
                                Builder\Block::make('heading')
                                    ->icon('heroicon-m-information-circle')
                                    ->schema([
                                        Forms\Components\RichEditor::make('heading')
                                            ->label('Introduction')
                                            ->required(),
                                    ])->maxItems(1),
                                Builder\Block::make('paragraph')
                                    ->icon('heroicon-m-bars-3-bottom-left')
                                    ->schema([
                                        Forms\Components\RichEditor::make('Paragraph')
                                            ->label('Paragraph')
                                            ->required(),
                                    ]),
                                Builder\Block::make('image')
                                    ->icon('heroicon-m-photo')
                                    ->schema([
                                        FileUpload::make('image')
                                            ->image()
                                            ->directory('image')
                                            ->required(),
                                    ]),
                            ])
                            ->blockIcons()
                            ->minItems(1)
                            ->blockNumbers(false)
                            ->default([
                                [
                                    'type' => 'heading',
                                ],[
                                    'type' => 'paragraph',
                                ],[
                                    'type' => 'paragraph',
                                ],[
                                    'type' => 'paragraph',
                                ],[
                                    'type' => 'paragraph',
                                ],
                            ])
                            ->collapsible(),
                    ])->grid(2)
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => Template::find($state['template_id'])?->name ?? null),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Forms Filled'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Filled By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->colors([
                        'warning' => 'Pending',
                        'danger' => 'Rejected',
                        'success' => 'Approved',
                    ])
                    ->icons([
                        'heroicon-m-x-mark' => 'Pending',
                        'heroicon-m-x-circle' => 'Rejected',
                        'heroicon-m-check-circle' => 'Approved',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn($record)=>$record->status != 'Approved'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record)=>$record->status != 'Approved' && auth()->check() && auth()->user()->can('Delete Form')),
                Tables\Actions\Action::make('detail')
                    ->label('View PDF')
                    ->color('info')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn($record)=>$record->status == 'Approved')
                    ->url(fn($record)=>"/admin/orders/details/$record->id"),
                Tables\Actions\Action::make('Approval')
                    ->label('Approve')
                    ->color('success')
                    ->requiresConfirmation()
                    ->slideOver()
                    ->icon('heroicon-m-check-circle')
                    ->action(fn($record)=>$record->update(['approved_by' => Auth::user()->id, 'status' =>'Approved']))
                    ->visible(fn($record)=>$record->status == 'Pending' && auth()->check() && auth()->user()->can('Approved Form')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->check() && auth()->user()->can('Delete Form'))
                        ->action(function (Collection $records) {
                            // Filter out records with 'Approved' status
                            $deletableRecords = $records->filter(fn ($record) => $record->status != 'Approved');

                            // Delete the filtered records
                            $deletableRecords->each->delete();
                        })
                        ->requiresConfirmation()
                        ->slideOver()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('Approve')
                        ->visible(fn () => auth()->check() && auth()->user()->can('Approved Form'))
                        ->action(function (Collection $records) {
                            // Filter out records with 'Approved' status
                            $deletableRecords = $records->filter(fn ($record) => $record->status != 'Approved');

                            // Delete the filtered records
                            $deletableRecords->each->update(['approved_by' => Auth::user()->id, 'status' =>'Approved']);
                        })
                        ->label('Approve')
                        ->color('success')
                        ->requiresConfirmation()
                        ->slideOver()
                        ->icon('heroicon-m-check-circle')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'details' => Pages\DetailsOrder::route('/details/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
