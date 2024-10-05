<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Setting';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    public static function shouldRegisterNavigation(): bool
    {
        return  Auth::user()->can('View Users');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name') // Assumes roles relationship exists
                    ->multiple()
                    ->preload()
                    ->required()
                    ->live()
                    ->label('Assign Role(s)')
                    ->afterStateUpdated(function ($state, ?User $record, callable $set) {
                        // Get selected roles by name
                        if ($record) {
                            // Get selected roles by name
                            $roles = Role::whereIn('name', $state)->get();

                            // Sync roles to the user
                            $record->syncRoles($roles);

                            // Fetch permissions for the selected roles
                            $permissions = $roles->flatMap->permissions->pluck('name')->unique();

                            // Dynamically set the permissions options in the form
                            $set('permissions', $permissions->toArray());
                        }
                    }),
                Forms\Components\Select::make('permissions')
                    ->relationship('permissions', 'name') // Assumes roles relationship exists
                    ->multiple()
                    ->preload()
                    ->required()
                    ->required()
                    ->live()
                    ->label('Assign Permission(s)')
                    ->afterStateUpdated(function ($state, ?User $record) {
                        if ($record) {
                            // Sync selected permissions to the user
                            $permissions = Permission::whereIn('name', $state)->get();
                            $record->syncPermissions($permissions); // Sync permissions
                        }
                    }),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('Privileges')
                        ->icon('heroicon-m-finger-print')
                        ->slideOver()
                        ->modalHeading(fn($record)=>$record->name.' Privileges')
                        ->form(function ($record){
                            return[
                                Select::make('role')
                                    ->relationship('roles', 'name') // Assumes roles relationship exists
                                    ->preload()
                                    ->required()
                                    ->default(optional($record->roles->first())->id)
                                    ->label('Assign Role(s)'),
                                Select::make('permissions')
                                    ->relationship('permissions', 'name') // Assumes roles relationship exists
                                    ->multiple()
                                    ->preload()
                                    ->required()
                                    ->default($record->permissions->pluck('id')->toArray())
                                    ->label('Assign Permission(s)'),
                            ];
                        })
                        ->action(function ($record, array $data){
                            if (isset($data['role'])) {
                                $role = Role::find($data['role']);
                                $record->syncRoles($role);
                            }

                            // Ensure the permissions are properly retrieved and synced
                            if (isset($data['permissions']) && is_array($data['permissions'])) {
                                $permissions = Permission::whereIn('id', $data['permissions'])->get();
                                $record->syncPermissions($permissions);
                            }
                        }),
                ])

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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
