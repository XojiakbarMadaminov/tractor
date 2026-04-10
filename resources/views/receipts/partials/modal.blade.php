@if($showReceipt)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div
            class="bg-white text-black dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto"
            style="max-height:90vh;"
            wire:click.stop
        >
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Savat Cheki</h3>
                <button wire:click="closeReceipt"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <x-heroicon-o-x-mark class="w-6 h-6"/>
                </button>
            </div>

            @include('receipts.partials.default', ['receipt' => $receiptData])

            <div class="flex gap-3 mt-6">
                <button wire:click="printReceipt"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-blue py-2 px-4 rounded-lg font-medium">
                    <x-heroicon-o-printer class="w-5 h-5 inline mr-2"/>
                    Chop etish
                </button>
                <button wire:click="closeReceipt"
                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-blue py-2 px-4 rounded-lg font-medium">
                    Yopish
                </button>
            </div>

        </div>
    </div>
@endif
