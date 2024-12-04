<div>
    <p>App Accesses</p>

    <form class="mt-5 p-4 bg-gray-200 rounded-lg space-y-4 text-gray-700">
        <h2 class="font-bold">Register App Access</h2>

        <div class="flex flex-row -mx-2 space-y-4 md:space-y-0 items-center">
            <div class="w-full px-2">
                <label class="block mb-1 flex flex-row gap-6" for="passkey">
                    <p>Passkey</p>
                </label>
                <input class="w-full h-10 px-3 text-base placeholder-gray-600 border rounded-lg focus:shadow-outline"
                    type="text" id="passkey" wire:model="passkey" />
                @error('passkey')
                    <p class="text-red-500">{{ $message }} </p>
                @enderror
            </div>
            <div class="w-full px-2">
                <label class="block mb-1 flex flex-row gap-6" for="name">
                    <p>Name</p>
                </label>
                <input class="w-full h-10 px-3 text-base placeholder-gray-600 border rounded-lg focus:shadow-outline"
                    type="text" id="name" wire:model="name" />
                @error('name')
                    <p class="text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <button class="bg-green-500 py-2 px-4 rounded-lg text-white h-10" wire:click.prevent="generate_key">
                Create
            </button>
        </div>
    </form>

    <div class="mt-5 relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Name</th>
                    <th scope="col" class="px-6 py-3">Hash</th>
                    <th scope="col" class="px-6 py-3">Created Date</th>
                    <th scope="col" class="px-6 py-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accesses as $access)
                    <tr
                        class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                        <th scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $access->name }}
                        </th>
                        <th scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $access->access }}
                        </th>
                        <td class="px-6 py-4">
                            {{ $access->created_at }}
                        </td>
                        <td class="px-6 py-4">
                            <button class="p-2 bg-red-600 rounded-md text-white"
                                wire:click.stop="delete('{{ $access->id }}')"
                                wire:confirm="Are you sure you want to delete this access key?">
                                Delete Key
                            </button>
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
