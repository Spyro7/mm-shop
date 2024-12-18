<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Grid::make()
                        ->schema([
                            TextInput::make('name')
                                ->label('Category name')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                                ->maxLength(255),
                            TextInput::make('slug')
                                ->required()
                                ->unique(Category::class, 'slug', ignoreRecord: true)
                                ->dehydrated()
                                ->maxLength(255),
                        ]),
                    Forms\Components\Grid::make()
                        ->schema([
                            Select::make('parent_id')
                                ->label('Parent Category')
                                ->relationship('parent', 'name')
                                ->nullable(),
                            Select::make('status')
                                ->label('Category Status')
                                ->options([
                                    '1' => 'Active',
                                    '0' => 'Inactive',
                                ])
                                ->default(1)
                                ->required(),
                        ])
                ]),
                Forms\Components\Section::make([
                    FileUpload::make('image')
                        ->label('Category Image')
                        ->directory('category_image')
                        ->image()
                        ->maxSize(2048),
                ]),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug'),
                Tables\Columns\TextColumn::make('parent.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\IconColumn::make('status')
                    ->label('Is Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\Filter::make('status_is_active')
                    ->query(fn (Builder $query) => $query->where('status', '1')),
                Tables\Filters\Filter::make('status_inactive')
                    ->query(fn (Builder $query) => $query->where('status', '0')),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])

            ->actions([
                Tables\Actions\ViewAction::make()
                    ->color('info'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getNavigationSort(): ?int
    {
        return 2;
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
