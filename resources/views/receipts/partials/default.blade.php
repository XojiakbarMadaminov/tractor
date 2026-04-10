@php
    $storeTitle = $receipt['title'] ?? 'Traktor ehtiyot qismlari';
    $subtitle   = $receipt['subtitle'] ?? 'SAVDO CHEKI';
    $number     = $receipt['receipt_number'] ?? '';
    $date       = $receipt['date'] ?? now()->format('d.m.Y H:i:s');
    $cartLabel  = $receipt['cart_label'] ?? 'Savat';
    $cartId     = $receipt['cart_id'] ?? null;
    $items      = $receipt['items'] ?? [];
    $totals     = $receipt['totals'] ?? ['qty' => 0, 'amount' => 0];
@endphp

<div id="receipt-content" class="receipt-content">
    <div class="center" style="margin-bottom:10px; margin-top:5px;">
        <h3>{{ $storeTitle }}</h3>
    </div>
    <div class="center bold" style="font-size:18px; margin-bottom:6px;">{{ $subtitle }}</div>
    <div style="text-align:center; margin-bottom:4px;">{{ $number }}</div>
    <div style="text-align:center; margin-bottom:8px;">{{ $date }}</div>

    @if($cartId)
        <div>{{ $cartLabel }}: #{{ $cartId }}</div>
    @endif

    <div class="line"></div>

    @forelse($items as $item)
        <div class="item-row">
            <span class="item-name">
                {{ $item['name'] }}<br>
                <span style="font-size:11px;">{{ $item['qty'] }} x {{ number_format($item['price'], 0, '.', ' ') }}</span>
            </span>
            <span class="item-total bold">{{ number_format($item['total'], 0, '.', ' ') }} so'm</span>
        </div>
    @empty
        <div class="text-center text-sm text-gray-500">Mahsulotlar mavjud emas</div>
    @endforelse

    <div class="line"></div>
    <div class="item-row">
        <span>Jami mahsulotlar:</span>
        <span>{{ $totals['qty'] ?? 0 }} dona</span>
    </div>
    <div class="item-row bold" style="font-size:15px;">
        <span>JAMI SUMMA:</span>
        <span>{{ number_format($totals['amount'] ?? 0, 0, '.', ' ') }} so'm</span>
    </div>
    <div class="center" style="margin-top:18px; font-size:12px;">
        {!! nl2br(e($receipt['footer_note'] ?? "Xaridingiz uchun rahmat!\nYana tashrifingizni kutamiz")) !!}
    </div>
    <div class="receipt-footer-printonly">
        <img src="{{ asset('images/traktor-qr.png') }}" alt="QR code"
             style="max-width:32mm; max-height:32mm;">
    </div>
</div>
