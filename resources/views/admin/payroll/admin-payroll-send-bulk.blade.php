<x-main.content>
    <x-main.title class="mb-8">Bulk Upload</x-main.title>

    <x-card.display-balance :balance="$this->balance_amount" color="primary" />

    @empty($file_data)
        <div class="relative rounded-lg">
            <div class="relative flex flex-col items-center justify-center my-5 py-9">
                <div class="py-9">
                    <img src="{{ url('images/placeholder/upload-file.svg') }}" alt="Upload File">
                </div>
                <p>Uploaded payroll will appear here!</p>
                <p class="mt-5">Drop your file here or click to choose a file</p>
                <x-button.filled-button class="mt-3" color="primary">
                    upload CSV file
                </x-button.filled-button>
                <input type="file" accept=".csv" class="absolute h-full w-full opacity-0 cursor-pointer"
                    wire:model="uploaded_csv_file" id="uploaded_csv_file">
                <div wire:loading wire:target="uploaded_csv_file">Uploading...</div>
                @error('uploaded_csv_file')
                    <span class="error text-red-700 mt-2">{{ $message }}</span>
                @enderror
            </div>
        </div>
    @else
        <div class="w-full my-6">
            <div class="flex flex-row justify-between">
                <h3 class="text-lg font-bold">Summary: Total - <span
                        class="text-red-500">{{ number_format($total_salary, 2) }}</span></h3>
                <div class="relative w-44 h-9">
                    <x-button.filled-button color="primary">
                        replace csv file
                    </x-button.filled-button>
                    <input type="file" accept=".csv" class="absolute hidden opacity-0 cursor-pointer w-44 h-9"
                        wire:model="uploaded_csv_file" id="uploaded_csv_file" />
                </div>
            </div>
            <table class="w-full border-collapse mt-5 table-fixed">
                <tr>
                    @foreach ($headers as $header)
                        <th class="text-left p-3 border min-w-5">{{ $header }}</th>
                    @endforeach
                    <th class="text-left p-3 border min-w-5">Remarks</th>
                </tr>
                @foreach ($file_data as $tableIdx => $row)
                    <tr>
                        @foreach ($headers as $key => $header)
                            <td
                                class="p-2 border break-words {{ in_array($key, $row['Errors'] ?? []) == true ? 'bg-red-200' : 'bg-white' }} {{ is_numeric($row[$key]) ? 'text-right' : 'text-left' }}">
                                {{ empty($row[$key]) ? '' : (is_numeric($row[$key]) && $key != 1 && $key != 4 ? number_format($row[$key], 2) : $row[$key]) }}
                            </td>
                        @endforeach
                        <td class="text-left p-2 border bg-white">
                            @if (empty($row['Remarks']))
                                <p class="text-green-600">
                                    No errors.
                                </p>
                            @else
                                <p class="text-red-600">
                                    @foreach ($row['Remarks'] as $remark)
                                        {{ $remark }} <br />
                                    @endforeach
                                </p>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>

            @if (!$allowSubmit)
                <p class="my-2 italic text-red-400">
                    * There are errors on your table, please refer to the 'Remarks'
                    column for more information.
                </p>
            @endif
            @if (!empty($savingErr) && $showSaveStatus)
                <p class="my-2 italic text-red-400">
                    * There are errors on saving, you can resubmit this table to retry.
                </p>
            @endif

            <div class="flex flex-row gap-2 mt-5">
                <x-button.outline-button wire:click='cancel_upload' color="primary" class="flex-1">
                    cancel
                </x-button.outline-button>
                <x-button.filled-button class="flex-1" color="primary" :disabled="!$allowSubmit" wire:click="save">
                    Submit
                </x-button.filled-button>
            </div>
        </div>
    @endempty



    {{-- DOWNLOAD --}}
    <div class="flex flex-row gap-2 mt-5">
        <x-icon.important />
        <p>IMPORTANT: RePay follows a very specific format for uploading excel files. Please use our formatted
            excel file in order to ensure a smooth process for uploading payroll. If you don’t have a copy of
            our format yet, you can <span class="underline font-semibold text-primary-600 cursor-pointer"
                wire:click="downloadFormat">
                download it here.
            </span>
        </p>
    </div>

    {{-- Error Message --}}
    <div>
        <div x-cloak x-show="$wire.apiErrorMsg"
            class="fixed inset-0 mw-100 mh-full z-100 bg-opacity-50 bg-black flex items-center justify-center"
            @click="$wire.apiErrorMsg=''">
            <div class="p-5 bg-white border-2 border-red-700 rounded-md w-96">
                <div class="flex flex-row justify-between items-center p-4">
                    <p class="text-red-600 text-lg">Error encountered!</p>
                    <div>
                        <x-icon.error />
                    </div>
                </div>
                <hr>
                <div class="p-4 text-red-700 font-bold">
                    {{ $apiErrorMsg }}
                </div>
                <hr>
                <small class="text-xs">Click anywhere inside this box to close</small>
            </div>
        </div>
    </div>


    {{-- Success Message --}}
    <div>
        <div x-cloak x-show="$wire.savingDone"
            class="fixed inset-0 mw-100 mh-full z-100 bg-opacity-50 bg-black flex items-center justify-center"
            @click="$wire.savingDone = false">
            <div class="p-5 bg-white border-2 border-green-700 rounded-md w-96">
                <div class="flex flex-row justify-between items-center p-4">
                    <p class="text-green-600 text-lg">Saving done!</p>
                    <div>
                        <x-icon.check />
                    </div>
                </div>
                <hr>
                <div class="p-4 text-green-700 font-bold">
                    Employee salary information saved!
                </div>
                <hr>
                <small class="text-xs">Click anywhere inside this box to close</small>
            </div>
        </div>
    </div>

    {{-- Loader --}}
    <x-loader.black-screen wire:loading>
        <x-loader.clock />
    </x-loader.black-screen>

</x-main.content>
