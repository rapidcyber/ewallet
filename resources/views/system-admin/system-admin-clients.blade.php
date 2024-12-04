<div>
    {{-- Success is as dangerous as failure. --}}
    <p>Partnered Merchant Credentials Manager</p>

    <form class="mt-5 p-4 bg-gray-200 rounded-lg space-y-4 text-gray-700">
        <h2 class="font-bold">Register Partnered Merchant</h2>

        <div class="flex flex-row -mx-2 space-y-4 md:space-y-0 items-end">
            <div class="w-full px-2">
                <label class="block mb-1 flex flex-row gap-6" for="name">
                    <p>Partner Account Number</p>
                    @error('account_number')
                        <p class="ml-6 text-red-500">{{ $message }}</p>
                    @enderror
                </label>
                <input class="w-full h-10 px-3 text-base placeholder-gray-600 border rounded-lg focus:shadow-outline"
                    type="text" id="name" wire:model="account_number" />
            </div>
            <button class="bg-green-500 py-2 px-4 rounded-lg text-white h-10" wire:click.prevent="createClient">
                Create
            </button>
        </div>
    </form>

    <div class="mt-5 relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Name</th>
                    <th scope="col" class="px-6 py-3">Partner ID</th>
                    <th scope="col" class="px-6 py-3">Client ID</th>
                    <th scope="col" class="px-6 py-3">Client Secret</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                    <tr
                        class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                        <th scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $client->name }}
                        </th>
                        <td class="px-6 py-4">
                            {{ $client->user_id }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $client->id }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $client->secret }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $client->revoked ? 'Revoked' : 'Active' }}
                        </td>
                        <td class="px-6 py-4">
                            @if (!$client->revoked)
                                <button
                                    class="p-2 {{ $client->revoked ? 'bg-green-600' : 'bg-orange-600' }} rounded-md text-white"
                                    wire:click.stop="revoke_client('{{ $client->id }}')"
                                    wire:confirm="Are you sure you want to {{ $client->revoked ? 'activate' : 'revoke' }} this credentials?">
                                    {{ $client->revoked ? 'Activate' : 'Revoke' }}
                                </button>
                                <button class="p-2 bg-red-600 rounded-md text-white"
                                    wire:click.stop="regenerate_secret('{{ $client->id }}')"
                                    wire:confirm="Are you sure you want to regenerate the secret?">
                                    Regen Secret
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
