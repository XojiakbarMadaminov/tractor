<?php

namespace App\Filament\Pages;

use App\Models\Sale;
use App\Models\Stock;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class SalesHistoryPage extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationLabel = 'Sotuv tarixi';
    protected static string|\UnitEnum|null $navigationGroup = 'Finance';
    protected static ?string $title = 'Sotuv tarixi';
    protected static ?string $slug = 'sales-history';
    protected string $view = 'filament.pages.sales-history-page';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?int $navigationSort = 3;

    public string $datePreset = 'week';
    public ?string $customStart = null;
    public ?string $customEnd = null;

    public function mount(): void
    {
        $this->setPreset('week');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sale::query()
                    ->with(['items.product', 'items.stock', 'cashier', 'store'])
                    ->withCount('items')
            )
            ->modifyQueryUsing(fn (Builder $query) => $this->applyDateFilter($query))
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->actions([
                Action::make('view_details')
                    ->label('Batafsil')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalHeading('Sotuv ma\'lumotlari')
                    ->modalContent(function (Sale $record) {
                        $sale = $record->loadMissing(['items.product', 'items.stock', 'cashier', 'store']);

                        return view('filament.pages.partials.sale-details', [
                            'sale'               => $sale
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Yopish'),
                Action::make('receipt')
                    ->label('Chek')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Sale $record): string => route('sale.receipt.print', $record))
                    ->openUrlInNewTab(),
            ])
            ->headerActions($this->getPresetActions())
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('Sotuvlar topilmadi')
            ->emptyStateDescription('Tanlangan davr uchun hech qanday sotuv qayd etilmagan.');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('created_at')
                ->label('Sana')
                ->dateTime('d.m.Y H:i')
                ->sortable()
                ->searchable(),
            TextColumn::make('cashier.name')
                ->label('Kassir')
                ->placeholder('-')
                ->toggleable(),
            TextColumn::make('total')
                ->label('Jami summa')
                ->numeric(decimalSeparator: '.', thousandsSeparator: ' ')
                ->suffix(" so'm")
                ->sortable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('stock_id')
                ->label('Sklad')
                ->options(fn () => Stock::query()->active()->pluck('name', 'id')->toArray())
                ->query(function (Builder $query, array $data): Builder {
                    $value = $data['value'] ?? null;

                    if (!filled($value)) {
                        return $query;
                    }

                    return $query->whereHas('items', fn (Builder $items) => $items->where('stock_id', $value));
                })
        ];
    }

    protected function getPresetActions(): array
    {
        return [
            Action::make('thisWeek')
                ->label('Bu hafta')
                ->color($this->datePreset === 'week' ? 'primary' : 'gray')
                ->action(fn () => $this->setPreset('week')),
            Action::make('thisMonth')
                ->label('Bu oy')
                ->color($this->datePreset === 'month' ? 'primary' : 'gray')
                ->action(fn () => $this->setPreset('month')),
            Action::make('customRange')
                ->label('Oraliq sana')
                ->color($this->datePreset === 'custom' ? 'primary' : 'gray')
                ->form([
                    DatePicker::make('start')
                        ->label('Boshlanish sana')
                        ->displayFormat('d.m.Y')
                        ->required(),
                    DatePicker::make('end')
                        ->label('Tugash sana')
                        ->displayFormat('d.m.Y')
                        ->required()
                        ->afterOrEqual('start'),
                ])
                ->fillForm(fn (): array => [
                    'start' => $this->customStart,
                    'end'   => $this->customEnd,
                ])
                ->action(function (array $data): void {
                    $this->setPreset('custom', $data['start'], $data['end']);
                }),
        ];
    }

    protected function setPreset(string $preset, ?string $start = null, ?string $end = null): void
    {
        $this->datePreset = $preset;

        [$startDate, $endDate] = match ($preset) {
            'week'   => [now()->startOfWeek(), now()->endOfWeek()],
            'month'  => [now()->startOfMonth(), now()->endOfMonth()],
            'custom' => [Carbon::parse($start)->startOfDay(), Carbon::parse($end)->endOfDay()],
            default  => [now()->startOfDay(), now()->endOfDay()],
        };

        $this->customStart = $startDate->toDateString();
        $this->customEnd   = $endDate->toDateString();
    }

    protected function applyDateFilter(Builder $query): Builder
    {
        if (!$this->customStart || !$this->customEnd) {
            return $query;
        }

        $start = Carbon::parse($this->customStart)->startOfDay();
        $end   = Carbon::parse($this->customEnd)->endOfDay();

        return $query->whereBetween('created_at', [$start, $end]);
    }
}
