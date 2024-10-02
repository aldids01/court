<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplateResource\Pages;
use App\Filament\Resources\TemplateResource\RelationManagers;
use App\Models\Template;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;

class TemplateResource extends Resource
{
    protected static ?string $model = Template::class;
    protected static ?string $modelLabel = 'Form';
    protected static ?string $navigationGroup = 'Forms';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationIcon = 'heroicon-o-scale';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()->schema([
                    TextInput::make('name')->label('Form Name'),
                    Forms\Components\Toggle::make('is_active')
                        ->inline(false)
                        ->onIcon('heroicon-m-check-circle')
                        ->offIcon('heroicon-m-x-circle')
                        ->default(true),
                ]),
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
                    ->collapsible()
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
                    ]),
                Forms\Components\Grid::make()->schema([
                    FileUpload::make('image_h')
                        ->image()
                        ->directory('header')
                        ->default('header/01J8AVVK7WKAH0R21W43V2QBQP.png')
                        ->label('Header Image'),
                    FileUpload::make('image_f')
                        ->image()
                        ->directory('footer')
                        ->default('footer/01J8AVVK8BHTZZQWJB105JNMWQ.png')
                        ->label('Footer Image'),
                ]),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image_h')
                    ->label('Header'),
                Tables\Columns\ImageColumn::make('image_f')
                    ->label('Footer'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTemplates::route('/'),
            'create' => Pages\CreateTemplate::route('/create'),
            'view' => Pages\ViewTemplate::route('/{record}'),
            'edit' => Pages\EditTemplate::route('/{record}/edit'),
        ];
    }
}
