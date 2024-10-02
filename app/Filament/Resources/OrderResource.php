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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $modelLabel = 'Fill Form';
    protected static ?string $navigationGroup = 'Forms';
    protected static ?string $recordTitleAttribute = 'client_name';
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('detail')
                ->label('Detail')
                ->color('info')
                ->icon('heroicon-o-document-magnifying-glass')
                ->url(fn ($record) => route('details', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'detail' => Pages\DetailsOrder::route('/detail/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
