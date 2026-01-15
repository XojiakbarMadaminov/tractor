<x-filament::page>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-300">
        @if($this->customStart && $this->customEnd)
            Tanlangan davr: {{ \Illuminate\Support\Carbon::parse($this->customStart)->format('d.m.Y') }} —
            {{ \Illuminate\Support\Carbon::parse($this->customEnd)->format('d.m.Y') }}
        @endif
    </div>
    {{ $this->table }}
</x-filament::page>
