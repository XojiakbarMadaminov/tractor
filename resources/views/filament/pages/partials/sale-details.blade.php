<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Sotuv ID</p>
            <p class="text-base font-semibold text-gray-900 dark:text-white">#{{ $sale->id }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Sana</p>
            <p class="text-base font-semibold text-gray-900 dark:text-white">
                {{ optional($sale->created_at)->format('d.m.Y H:i') }}
            </p>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Kassir</p>
            <p class="text-base font-semibold text-gray-900 dark:text-white">
                {{ $sale->cashier->name ?? '—' }}
            </p>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Jami summa</p>
            <p class="text-base font-semibold text-gray-900 dark:text-white">
                {{ number_format($sale->total, 0, '.', ' ') }} so'm
            </p>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Do'kon</p>
            <p class="text-base font-semibold text-gray-900 dark:text-white">
                {{ $sale->store->name ?? '—' }}
            </p>
        </div>
    </div>

    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Sotilgan mahsulotlar</h4>

        @if($sale->items->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/30 text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium">Mahsulot</th>
                        <th class="px-3 py-2 text-left font-medium">Sklad</th>
                        <th class="px-3 py-2 text-right font-medium">Miqdor</th>
                        <th class="px-3 py-2 text-right font-medium">Narxi</th>
                        <th class="px-3 py-2 text-right font-medium">Jami</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 text-gray-900 dark:text-gray-100">
                    @foreach($sale->items as $item)
                        <tr>
                            <td class="px-3 py-2">
                                <p class="font-medium">{{ $item->product->name ?? 'Noma\'lum mahsulot' }}</p>
                                <p class="text-xs text-gray-500">#{{ $item->product->code ?? '—' }}</p>
                            </td>
                            <td class="px-3 py-2">
                                {{ $item->stock->name ?? '—' }}
                            </td>
                            <td class="px-3 py-2 text-right">
                                {{ $item->qty }} dona
                            </td>
                            <td class="px-3 py-2 text-right">
                                {{ number_format($item->price, 0, '.', ' ') }} so'm
                            </td>
                            <td class="px-3 py-2 text-right font-semibold">
                                {{ number_format($item->subtotal, 0, '.', ' ') }} so'm
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center text-sm text-gray-500 dark:text-gray-400 py-6">
                Tovarlar mavjud emas
            </div>
        @endif
    </div>
</div>
