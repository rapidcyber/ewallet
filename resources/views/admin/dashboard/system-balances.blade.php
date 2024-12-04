<div class="p-6">
    {{-- Nothing in the world is as soft and yielding as water. --}}
    <div class="rounded-lg bg-white p-4">
        <p class="font-medium text-lg">INTERNAL BALANCES</p>
        <p>- See Repay's overall balance of cash-in and cash-out per provider</p>
    </div>
    <div class="rounded-lg bg-white p-4 mt-4">
        <p class="font-medium text-lg">TODO : show balance per provider (transaction_provider)</p>
    </div>

    <hr class="h-4 mx-auto my-4 bg-black border-0 rounded md:my-10">

    <div class="rounded-lg bg-white p-4">
        <p class="font-medium text-lg">EXTERNAL BALANCES</p>
        <p>- See Repay's external balance per provider <small class="italic">(if available)</small> </p>
    </div>
    <div class="flex flex-row gap-4">

        <div class="rounded-lg bg-white p-4 mt-4">
            <p class="font-medium text-lg mb-2">ALLBANK</p>

            <p class="font-medium text-xl">PHP {{ number_format($allbank['available_balance'], 2) }}</p>
            <p>Available Balance </p>
            <p class="mt-2 font-medium text-xl">PHP {{ number_format($allbank['current_balance'], 2) }}</p>
            <p>Current Balance </p>


            <div class="mt-2">

                <button type="button" wire:click="alb_p2m_transactions">
                    P2M TRANSACTIONS
                </button>
                <button type="button" wire:click="alb_opc_transactions" class="ml-2">
                    OPC TRANSACTIONS
                </button>
            </div>
        </div>
    </div>
</div>
