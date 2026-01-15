<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Sotuv cheki</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @page { size: 80mm auto; margin: 0 }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .print-wrapper {
            display: flex;
            justify-content: center;
            padding: 12px 0;
        }
        .receipt {
            width: 76mm;
            margin: 0 auto;
            padding-left: 2mm;
            padding-right: 2mm;
            page-break-inside: avoid;
        }
        .center { text-align: center }
        .right { text-align: right }
        .bold { font-weight: 700 }
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin: 1mm 0;
            page-break-inside: avoid;
        }
        .item-name { flex: 1 }
        .item-total { text-align: right; min-width: 24mm }
        .line { border-bottom: 1px dashed #000; margin: 2mm 0 }
        .receipt img {
            display: block;
            margin: 0 auto 2mm auto;
            max-width: 40mm;
            max-height: 40mm;
        }
        .actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 16px;
        }
        .actions button,
        .actions a {
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        .actions button {
            background: #2563eb;
            color: #fff;
        }
        .actions a {
            background: #e5e7eb;
            color: #111827;
        }
        @media print {
            .actions {
                display: none;
            }
        }
    </style>
    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 300));
    </script>
</head>
<body>
<div class="print-wrapper">
    <div class="receipt">
        @include('receipts.partials.default', ['receipt' => $receipt])
        <div class="actions">
            <button onclick="window.print()">Chop etish</button>
            <a href="{{ url()->previous() }}">Yopish</a>
        </div>
    </div>
</div>
</body>
</html>
