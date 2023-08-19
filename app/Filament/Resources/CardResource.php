<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages;
use App\Filament\Resources\CardResource\RelationManagers;
use App\Models\Card;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('to_id')
                    ->label('To User')
                    ->placeholder('Select a user')
                    ->searchable()
                    ->options(
                        User::query()
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(function (User $user) {
                                return [$user->getKey() => $user->name];
                            })
                    )
                    ->required(),
                Forms\Components\Select::make('value')
                    ->label('Value')
                    ->placeholder('Select a value')
                    ->options([
                        'Value 1' => 'Value 1',
                        'Value 2' => 'Value 2',
                        'Value 3' => 'Value 3',
                    ])
                    ->required(),
                Forms\Components\Hidden::make('from_id')
                    ->default(fn () => auth()->id()),
                Forms\Components\RichEditor::make('body')
                    ->label('Body')
                    ->required()
                    ->columnSpan(2),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
//                id
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\ImageColumn::make('from.avatar')
                    ->label('')
                    ->circular(),
                Tables\Columns\TextColumn::make('from.name')
                    ->label('From User')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('to.name')
                    ->label('To User')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->toggleable()
                    ->description(fn(Card $card) => \Str::limit(strip_tags($card->body), 50))
                    ->tooltip(fn(Card $card) => strip_tags($card->body)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Filter::make('created_at')
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
                    }),
                TernaryFilter::make('trashed')
                    ->placeholder('Without trashed records')
                    ->trueLabel('With trashed records')
                    ->falseLabel('Only trashed records')
                    ->queries(
                        true: fn (Builder $query) => $query->withTrashed(),
                        false: fn (Builder $query) => $query->onlyTrashed(),
                        blank: fn (Builder $query) => $query->withoutTrashed(),
                    )
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make()
                ]),
                Tables\Actions\DeleteBulkAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('User deleted')
                            ->body('The user has been deleted successfully.'),
                    ),
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('User deleted')
                            ->body('The user has been deleted successfully.'),
                    ),
                Tables\Actions\RestoreBulkAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('User restored')
                            ->body('The user has been restored successfully.'),
                    ),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->persistSearchInSession()
            ->persistColumnSearchesInSession();
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
            'index' => Pages\ListCards::route('/'),
            'create' => Pages\CreateCard::route('/create'),
            'edit' => Pages\EditCard::route('/{record}/edit'),
        ];
    }
}
