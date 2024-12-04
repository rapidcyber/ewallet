<div x-data="dragDrop{{ $function_name }}" @drop.prevent="drop" @dragover.prevent>
    {{-- Empty --}}
    @if (count($uploaded_images) === 0)
        <div class="flex flex-col w-full" {{-- x-show="image_count == 0" --}}>
            {{-- <p class="block text-xs 2xl:text-sm text-rp-neutral-500">Upload Images</p> --}}
            <div class="flex flex-col gap-4 justify-center items-center p-10 rounded-lg shadow-[0_0_8px_0_#00000040_inset] bg-rp-neutral-50">
                <x-icon.upload />
                <p class="text-rp-neutral-600 font-bold">Drag and drop image files to upload</p>
                <button type="button" @click="$refs.requestReturn.click();" class="px-5 py-2 {{ $color }} font-bold rounded-[5px] shadow-[0_1px_4px_0_#00000040] bg-white uppercase">add images</button>
                <input wire:model="images" type="file" x-ref="requestReturn" class="hidden" accept="image/png, image/jpeg" multiple>
            </div>
        </div>
    @elseif (count($uploaded_images) > 0)
        {{-- with images --}}
        <div class="px-4 py-3 bg-rp-neutral-100 rounded-xl" x-data="{ handleSort: (item, position) => { $wire.sortImages(item, position) } }">
            <h3 class="font-bold">Images</h3>
            <div x-sort="handleSort" x-sort.ghost class="flex flex-col gap-3 mt-4">
                @if ($uploaded_images)
                    @foreach ($uploaded_images as $key => $image)
                        {{-- Image --}}
                        <div x-sort:item="{{ $key }}" class="break-words flex flex-row items-center gap-4 cursor-grab" wire:key='{{ $function_name }}-image-{{ $key }}'>
                            <div class="w-max">
                                {{-- Sort --}}
                                <div>
                                    <x-icon.three-bars />
                                </div>
                            </div>
                            <div class="flex flex-row items-center w-11/12">
                                <div class="space-y-2 w-max">
                                    {{-- Image Preview --}}
                                    <div class="w-44 h-28 bg-rp-neutral-400 rounded-lg">
                                        @php
                                            $src = $image['id'] === null ? $image['image']->temporaryUrl() : $image['image'];
                                        @endphp
                                        <img src="{{ $src }}" class="w-full h-full object-cover object-center rounded-lg" alt="">
                                </div>
                                    <div class="flex gap-2">
                                        {{-- Replace image --}}
                                        <button class="p-1 bg-white rounded-md cursor-pointer" @click="$wire.replaceImage({{ $key }});document.getElementById('replaceImage{{ $function_name }}').click();">
                                           <x-icon.replace-image />
                                        </button>
                                        
                                        {{-- Delete image --}}
                                        <button class="p-1 bg-white rounded-md cursor-pointer" wire:click="removeImage({{ $key }})">
                                            <x-icon.delete-image />
                                        </button>
                                    </div>
                                </div>
                                <div class="w-1/2 px-2">
                                    <p class="text-rp-neutral-600 font-bold">{{ $image['name'] }}</p>
                                    <p>{{ $image['size'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <input wire:model="replacement_image" type="file" class="hidden" name="replaceImage{{ $function_name }}" accept="image/png, image/jpeg" id="replaceImage{{ $function_name }}">
        </div>
    @endif

    @error('images.*')  <p class="text-sm text-red-600">{{ $message }}</p> @enderror
    @error('images')  <p class="text-sm text-red-600">{{ $message }}</p> @enderror
    @error('replacement_image')  <p class="text-sm text-red-600">{{ $message }}</p> @enderror

    @script
        <script>
            Alpine.data('dragDrop{{ $function_name }}', () => ({
                drop(e) {
                    if (event.dataTransfer.files.length > 0) {
                        const files = e.dataTransfer.files
                        this.uploadFiles(files)
                    }
                },
                uploadFiles(files){
                    @this.uploadMultiple('images', files, (success) => {
                        console.log('success upload');
                    }, (error) => {

                    });
                }
            }));
        </script>
    @endscript
</div>

    
