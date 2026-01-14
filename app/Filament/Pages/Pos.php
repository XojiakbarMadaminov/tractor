<?php

namespace App\Filament\Pages;

use App\Models\Sale;
use App\Models\Product;
use Filament\Pages\Page;
use Livewire\Attributes\On;
use App\Models\ProductStock;
use App\Services\CartService;
use Filament\Notifications\Notification;
use Filament\Panel\Concerns\HasNotifications;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Support\Collection as EloquentCollection;

class Pos extends Page
{
    use HasNotifications, HasPageShield;

    protected static ?string $title                          = 'Sotuv';
    protected string $view                                   = 'filament.pages.pos';
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?int $navigationSort                    = 1;

    public function getHeading(): string
    {
        return '';
    }

    public string $barcodeSearch = '';
    public string $codeSearch    = '';
    public int $activeCartId     = 1; // Joriy faol cart ID
    public bool $showReceipt     = false; // Chek ko'rsatish uchun
    public array $receiptData    = []; // Chek ma'lumotlari

    /** @var EloquentCollection<int, Product> */
    public EloquentCollection $products;

    public array $cart        = [];
    public array $totals      = ['qty' => 0, 'amount' => 0];
    public array $activeCarts = []; // Barcha faol cartlar ro'yxati

    public function mount(): void
    {
        $this->products = new EloquentCollection;
        $this->refreshActiveCarts();

        // Oxirgi faol cart ID ni session dan olish
        $savedCartId = session('pos_active_cart_id', 1);

        // Agar saqlangan cart ID hali ham mavjud bo'lsa, uni ishlatish
        if (!empty($this->activeCarts) && array_key_exists($savedCartId, $this->activeCarts)) {
            $this->activeCartId = $savedCartId;
        } else {
            // Agar saqlangan cart yo'q bo'lsa, birinchi mavjud cartni tanlash
            if (!empty($this->activeCarts)) {
                $this->activeCartId = array_key_first($this->activeCarts);
            } else {
                $this->activeCartId = 1;
            }
            session()->put('pos_active_cart_id', $this->activeCartId);
        }

        $this->refreshCart();
    }

    /* ---------- Cart boshqaruvi ---------- */
    public function switchCart(int $cartId): void
    {
        $this->activeCartId = $cartId;
        // Faol cart ID ni session ga saqlash
        session()->put('pos_active_cart_id', $cartId);

        $this->refreshCart();
        $this->clearSearchInputs();
    }

    public function createNewCart(): void
    {
        $cartService = app(CartService::class);
        $activeCarts = $cartService->getActiveCartIds();

        // Yangi cart ID ni topish
        $newCartId = 1;
        while (in_array($newCartId, $activeCarts)) {
            $newCartId++;
        }

        $this->activeCartId = $newCartId;
        // Yangi faol cart ID ni session ga saqlash
        session()->put('pos_active_cart_id', $newCartId);

        $this->refreshCart();
        $this->refreshActiveCarts();

        Notification::make()
            ->title("Yangi savat #{$newCartId} yaratildi")
            ->success()
            ->send();
    }

    public function closeCart(int $cartId): void
    {
        $cartService = app(CartService::class);

        // Faol cartlar sonini tekshirish (bo'sh cartlarni ham hisobga olish)
        $allActiveCarts = array_keys($this->activeCarts);
        if (count($allActiveCarts) <= 1) {
            Notification::make()
                ->title('Kamida bitta savat ochiq bo\'lishi kerak')
                ->warning()
                ->send();

            return;
        }

        $cartService->clear($cartId);

        // Agar yopilayotgan cart joriy faol cart bo'lsa, boshqasini tanlash
        if ($this->activeCartId === $cartId) {
            $remainingCarts     = array_filter($allActiveCarts, fn ($id) => $id !== $cartId);
            $this->activeCartId = reset($remainingCarts) ?: 1;
            session()->put('pos_active_cart_id', $this->activeCartId);
        }

        $this->refreshActiveCarts();
        $this->refreshCart();

        Notification::make()
            ->title("Savat #{$cartId} yopildi")
            ->success()
            ->send();
    }

    /* ---------- Qidiruv ---------- */
    public function updatedBarcodeSearch(): void
    {
        $this->searchProducts($this->barcodeSearch, ['barcode', 'name']);
    }

    public function updatedCodeSearch(): void
    {
        $this->searchProducts($this->codeSearch, ['code', 'name']);
    }

    /* ---------- Savat operatsiyalari ---------- */
    public function add(int $id): void
    {
        app(CartService::class)->add(Product::findOrFail($id), 1, $this->activeCartId);
        $this->refreshCart();
        $this->refreshActiveCarts();
    }

    public function updateQty(int $id, int $qty)
    {
        $cartService = app(CartService::class);

        $cart = $cartService->all($this->activeCartId);
        $row  = $cart[$id] ?? null;

        if (!$row || empty($row['stock_id'])) {
            Notification::make()
                ->title('Avval skladni tanlang')
                ->danger()
                ->send();

            return false;
        }

        $available = ProductStock::where('product_id', $id)
            ->where('stock_id', $row['stock_id'])
            ->value('quantity');

        if ($qty > $available) {
            Notification::make()
                ->title('Yetarli miqdor yo‘q')
                ->body("Skladda faqat {$available} dona mavjud.")
                ->danger()
                ->send();

            return false;
        }

        $cartService->update($id, $qty, $this->activeCartId);

        $this->refreshCart();
        $this->refreshActiveCarts();
    }

    public function remove(int $id): void
    {
        app(CartService::class)->remove($id, $this->activeCartId);
        $this->refreshCart();
        $this->refreshActiveCarts();
    }

    /* ---------- Checkout ---------- */
    public function checkout(): void
    {
        $cartService = app(CartService::class);
        $totals      = $cartService->totals($this->activeCartId);

        if (!$totals['qty']) {
            Notification::make()
                ->title('Savat bo\'sh')
                ->warning()
                ->send();

            return;
        }

        $cartItems = $cartService->all($this->activeCartId);

        foreach ($cartItems as $row) {
            if (empty($row['stock_id'])) {
                Notification::make()
                    ->title("{$row['name']} uchun sklad tanlanmagan")
                    ->danger()
                    ->send();

                return;
            }

            $available = ProductStock::where('product_id', $row['id'])
                ->where('stock_id', $row['stock_id'])
                ->value('quantity');

            if ($row['qty'] > $available) {
                Notification::make()
                    ->title("{$row['name']} uchun yetarli miqdor yo‘q")
                    ->body("Skladda faqat {$available} dona mavjud.")
                    ->danger()
                    ->send();

                return;
            }
        }

        $this->prepareReceipt($this->activeCartId, $cartItems, $totals);

        \DB::transaction(function () use ($cartService, $totals) {
            $sale = Sale::create([
                'store_id' => auth()->user()->current_store_id,
                'total'    => $totals['amount'],
            ]);

            foreach ($cartService->all($this->activeCartId) as $row) {
                $sale->items()->create([
                    'product_id' => $row['id'],
                    'qty'        => $row['qty'],
                    'price'      => $row['price'],
                    'subtotal'   => $row['qty'] * $row['price'],
                ]);

                ProductStock::where('product_id', $row['id'])
                    ->where('stock_id', $row['stock_id'])
                    ->decrement('quantity', $row['qty']);
            }
        });

        $cartService->clear($this->activeCartId);
        $this->clearSearchInputs();
        $this->refreshCart();
        $this->refreshActiveCarts();

        Notification::make()
            ->title("Savat #{$this->activeCartId} da sotuv yakunlandi")
            ->success()
            ->send();
    }

    /* ---------- Chek funksiyalari ---------- */
    public function prepareReceipt(int $cartId, array $items, array $totals): void
    {
        $this->receiptData = [
            'cart_id'        => $cartId,
            'items'          => $items,
            'totals'         => $totals,
            'date'           => now()->format('d.m.Y H:i:s'),
            'receipt_number' => 'R' . str_pad($cartId, 4, '0', STR_PAD_LEFT) . time(),
        ];

        $this->showReceipt = true;
    }

    public function printReceipt(): void
    {
        $this->dispatch('print-receipt');
    }

    public function closeReceipt(): void
    {
        $this->showReceipt = false;
        $this->receiptData = [];
    }

    /* ---------- Helper metodlar ---------- */
    #[On('refresh-cart')]
    public function refreshCart(): void
    {
        $cartService  = app(CartService::class);
        $this->cart   = $cartService->all($this->activeCartId);
        $this->totals = $cartService->totals($this->activeCartId);
    }

    public function refreshActiveCarts(): void
    {
        $cartService       = app(CartService::class);
        $this->activeCarts = [];

        // Barcha mavjud cartlarni olish (bo'sh ham, to'la ham)
        $allCartIds = $cartService->getAllCartIds();

        if (empty($allCartIds)) {
            // Agar hech qanday cart bo'lmasa, birinchi cartni yaratish
            $this->activeCarts[1] = ['qty' => 0, 'amount' => 0];
        } else {
            // Barcha cartlar uchun ma'lumotlarni olish
            foreach ($allCartIds as $cartId) {
                $this->activeCarts[$cartId] = $cartService->totals($cartId);
            }
        }
    }

    /* ---------- Skaner metodlari ---------- */
    public function scanEnter(): void
    {
        $value     = trim($this->barcodeSearch);
        $fieldName = 'barcodeSearch';

        if ($value === '') {
            $value     = trim($this->codeSearch);
            $fieldName = 'codeSearch';
        }

        if ($value === '') {
            return;
        }

        $product = Product::where('barcode', $value)
            ->orWhere('code', $value)
            ->first();
        if ($product) {
            $this->add($product->id);
            $this->clearSearchInputs($fieldName);
        } else {
            Notification::make()
                ->title('Mahsulot topilmadi')
                ->danger()
                ->send();
        }
    }

    public function addByBarcode(string $value): void
    {
        $value = trim($value);
        if (!$value) {
            return;
        }

        $product = Product::where('barcode', $value)
            ->orWhere(function ($q) use ($value) {
                $q->where('name', 'ILIKE', "{$value}%")
                    ->orWhere('name', 'ILIKE', "%{$value}");
            })
            ->first();

        if ($product) {
            app(CartService::class)->add($product, 1, $this->activeCartId);
            $this->clearSearchInputs('barcodeSearch');
            $this->refreshCart();
            $this->refreshActiveCarts();

            Notification::make()
                ->title("Savat #{$this->activeCartId} ga qo'shildi")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Mahsulot topilmadi')
                ->danger()
                ->send();
        }
    }

    public function addByCode(string $value): void
    {
        $value = trim($value);
        if (!$value) {
            return;
        }

        $product = Product::where('code', $value)->first();

        if ($product) {
            app(CartService::class)->add($product, 1, $this->activeCartId);
            $this->clearSearchInputs('codeSearch');
            $this->refreshCart();
            $this->refreshActiveCarts();

            Notification::make()
                ->title("Savat #{$this->activeCartId} ga qo'shildi")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Mahsulot topilmadi')
                ->danger()
                ->send();
        }
    }

    public function updatePrice(int $id, float $price)
    {
        try {
            app(CartService::class)->updatePrice($id, $price, $this->activeCartId);
            $this->refreshCart();
            $this->refreshActiveCarts();

            return true;
        } catch (\InvalidArgumentException $e) {
            Notification::make()
                ->title('Narx noto‘g‘ri')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return false;
        }
    }

    public function updateStock(int $productId, int $stockId): void
    {
        app(CartService::class)->updateStock($productId, $stockId, $this->activeCartId);

        $this->refreshCart();
        $this->refreshActiveCarts();
    }

    protected function searchProducts(string $value, array $columns): void
    {
        $value = trim($value);

        if ($value === '') {
            $this->products = new EloquentCollection;

            return;
        }

        $exactColumns        = ['code', 'barcode'];
        $prioritizeExactCode = in_array('code', $columns, true);

        $this->products = Product::query()
            ->where(function ($query) use ($value, $columns, $exactColumns) {
                foreach ($columns as $index => $column) {
                    $isExact = in_array($column, $exactColumns, true);

                    if ($index === 0) {
                        if ($isExact) {
                            $query->where($column, $value);
                        } else {
                            $query->where($column, 'ILIKE', "%{$value}%");
                        }
                    } else {
                        if ($isExact) {
                            $query->orWhere($column, $value);
                        } else {
                            $query->orWhere($column, 'ILIKE', "%{$value}%");
                        }
                    }
                }
            })
            ->when($prioritizeExactCode, fn ($q) => $q->orderByRaw('CASE WHEN code = ? THEN 0 ELSE 1 END', [$value]))
            ->orderBy('name')
            ->limit(15)
            ->get();
    }

    protected function clearSearchInputs(?string $property = null): void
    {
        if ($property) {
            $this->reset($property);
        } else {
            $this->reset('barcodeSearch', 'codeSearch');
        }

        $this->products = new EloquentCollection;
    }
}
