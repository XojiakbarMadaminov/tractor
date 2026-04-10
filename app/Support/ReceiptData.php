<?php

namespace App\Support;

use App\Models\Sale;

class ReceiptData
{
    /**
     * Create a receipt payload from a persisted sale.
     */
    public static function fromSale(Sale $sale): array
    {
        $sale->loadMissing(['items.product', 'cashier', 'store']);

        $items = $sale->items->map(function ($item) {
            return [
                'name'  => $item->product->name ?? ('Mahsulot #' . $item->product_id),
                'qty'   => (int) $item->qty,
                'price' => (float) $item->price,
                'total' => (float) $item->subtotal,
            ];
        })->all();

        return [
            'title'          => $sale->store?->name ?? config('app.name'),
            'subtitle'       => 'SAVDO CHEKI',
            'receipt_number' => self::formatReceiptNumber($sale->id),
            'date'           => optional($sale->created_at)->format('d.m.Y H:i:s'),
            'cart_label'     => 'Sotuv',
            'cart_id'        => (string) $sale->id,
            'items'          => $items,
            'totals'         => [
                'qty'    => $sale->items->sum('qty'),
                'amount' => (float) $sale->total,
            ],
            'footer_note'    => "Xaridingiz uchun rahmat!\nYana tashrifingizni kutamiz",
        ];
    }

    /**
     * Create a receipt payload from POS cart data before persisting.
     */
    public static function fromCart(int $cartId, array $items, array $totals, array $meta = []): array
    {
        $collection = collect($items);

        $normalizedItems = $collection->map(function (array $item) {
            $qty   = (int) ($item['qty'] ?? 0);
            $price = (float) ($item['price'] ?? 0);

            return [
                'name'  => $item['name'] ?? 'Noma\'lum',
                'qty'   => $qty,
                'price' => $price,
                'total' => $qty * $price,
            ];
        })->values()->all();

        $qtyTotal = array_sum(array_column($normalizedItems, 'qty'));

        return [
            'title'          => $meta['store_name'] ?? auth()->user()?->currentStore?->name ?? config('app.name'),
            'subtitle'       => 'SAVDO CHEKI',
            'receipt_number' => self::formatCartReceiptNumber($cartId),
            'date'           => now()->format('d.m.Y H:i:s'),
            'cart_label'     => 'Savat',
            'cart_id'        => (string) $cartId,
            'items'          => $normalizedItems,
            'totals'         => [
                'qty'    => (int) ($totals['qty'] ?? $qtyTotal),
                'amount' => (float) ($totals['amount'] ?? 0),
            ],
            'footer_note'    => $meta['footer_note'] ?? "Xaridingiz uchun rahmat!\nYana tashrifingizni kutamiz",
        ];
    }

    protected static function formatReceiptNumber(int $id): string
    {
        return 'S' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    protected static function formatCartReceiptNumber(int $cartId): string
    {
        return 'POS-' . str_pad((string) $cartId, 2, '0', STR_PAD_LEFT) . '-' . now()->format('ymdHi');
    }
}
