<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Product information')->schema([
                        TextInput::make('name')
                            ->columnSpanFull()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                if ($operation !== 'create') {
                                    return;
                                }
                                $set('slug', Str::slug($state));
                            })
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->columnSpanFull()
                            ->required()
                            ->maxLength(255)
                            ->unique(Product::class, 'slug', ignoreRecord: true)
                            ->disabled()
                            ->dehydrated(),
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->placeholder('Select a Category')
                            ->options(Category::all()->pluck('name', 'id'))
                            ->required(),
                        Forms\Components\Select::make('brand_id')
                            ->label('Brand')
                            ->placeholder('Select a Brand')
                            ->options(Brand::all()->pluck('name', 'id'))
                            ->required(),
                        MarkdownEditor::make('description')
                            ->columnSpanFull()
                            ->fileAttachmentsDirectory('products'),
                        Forms\Components\TextInput::make('specifications')->columnSpanFull(),
                    ])->columns(2),
                    Section::make('Product images')->schema([
                        FileUpload::make('images')
                            ->required()
                            ->multiple()
                            ->maxSize(1024)
                            ->directory('products')
                            ->maxFiles(6)
                            ->minFiles(1)
                            ->panelLayout('grid')
                            ->imageResizeMode('cover')
                            ->appendFiles()
                            ->reorderable(),
                    ]),
                ])->columnSpan(2),
                Group::make()->schema([
                    Forms\Components\Section::make('Product price details')->schema([
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        Forms\Components\TextInput::make('discounted_price')
                            ->numeric(),
                        Forms\Components\TextInput::make('shipping')
                            ->numeric(),
                    ])->columnSpan(1),
                    Section::make('Product details')->schema([
                        Forms\Components\TextInput::make('stock_quantity')
                            ->required()
                            ->numeric()
                            ->default(0),
                        Forms\Components\Repeater::make('color')
                            ->label('Colors')
                            ->schema([
                                Forms\Components\ColorPicker::make('color')
                                    ->label('Color')
                            ])
                            ->collapsible(),
                        Forms\Components\TextInput::make('rating')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('reviews')
                            ->numeric()
                            ->default(0),
                    ])->columnSpan(1),
                    Section::make('Product status')->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->required(),
                        Forms\Components\Toggle::make('in_stock')
                            ->required(),
                        Forms\Components\Toggle::make('is_featured')
                            ->required(),
                        Forms\Components\Toggle::make('on_sale')
                            ->required(),
                    ])
                ]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discounted_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reviews')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('on_sale')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\IconColumn::make('in_stock')
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
