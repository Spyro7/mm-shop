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

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

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
                            ->directory('product_images')
                            ->maxFiles(6)
                            ->minFiles(1)
                            ->panelLayout('grid')
                            ->imageResizeMode('cover')
                            ->appendFiles()
                            ->reorderable(),
                    ]),
                ])->columnSpan(2),
                Group::make()->schema([
                    Forms\Components\Section::make('Product details')->schema([
                        TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->required()
                            ->suffix('USD'),

                        TextInput::make('discount_percentage')
                            ->label('Discount (%)')
                            ->numeric()
                            ->suffix('%')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $price = $get('price');
                                if ($price && $state) {
                                    $discountedPrice = $price - ($price * $state / 100);
                                    $set('discounted_price', round($discountedPrice, 2));
                                }
                            }),

                        TextInput::make('discounted_price')
                            ->label('Discounted Price')
                            ->numeric()
                            ->suffix('USD')
                            ->dehydrated()
                            ->readOnly()
                            ->disabled(false),

                        TextInput::make('shipping')
                            ->label('Shipping Cost')
                            ->numeric()
                            ->suffix('USD')
                            ->placeholder('Enter shipping cost'),
                        Forms\Components\TextInput::make('stock_quantity')
                            ->required()
                            ->numeric()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('in_stock', $state > 0);
                            }),
                    ])->columnSpan(1),
                    Section::make('Product colors')->schema([
                        Forms\Components\Repeater::make('color')
                            ->label('Colors')
                            ->schema([
                                Forms\Components\ColorPicker::make('color')
                                    ->label('Color')
                            ])
                            ->collapsible(),
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
                Tables\Columns\ImageColumn::make('images')
                ->limit(1),
                Tables\Columns\TextColumn::make('name')
                    ->label('Product name')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discounted_price')
                    ->label('Discount')
                    ->numeric()
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping')
                    ->numeric()
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Quantity'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('on_sale')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->boolean(),
                Tables\Columns\IconColumn::make('in_stock')
                    ->toggleable(isToggledHiddenByDefault: true)
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
                    Tables\Actions\EditAction::make()
                        ->color('primary'),
                    Tables\Actions\ViewAction::make()
                        ->color('info'),
                    Tables\Actions\DeleteAction::make()
                ])
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
        return 3;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
