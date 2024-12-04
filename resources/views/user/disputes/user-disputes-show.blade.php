<x-main.content>
    <x-main.title class="mb-8">
        Dispute Details
    </x-main.title>

    <x-layout.details.more-details>
        <x-layout.details.more-details.section title="General Details">
            <div class="space-y-2">
                <div class="flex gap-2 items-center break-words w-full">
                    <p class="text-base w-1/3">Status</p>
                    <div class="w-2/3">
                        @switch($dispute->status)
                            @case('pending')
                                <x-status color="neutral" class="w-max">Pending</x-status>
                                @break
                            @case('partially-paid')
                                <x-status color="green" class="w-max">Resolved - Partially Paid</x-status>
                                @break
                            @case('fully-paid')
                                <x-status color="green" class="w-max">Resolved - Fully Paid</x-status>
                                @break
                            @case('denied')
                                <x-status color="red" class="w-max">Denied</x-status>
                                @break
                            @default
                                <x-status color="red" class="w-max">{{ $dispute->status }}</x-status>
                        @endswitch
                    </div>
                </div>
                <x-layout.details.more-details.data-field field="Category" value="{{ $dispute->reason->name }}" />
                <x-layout.details.more-details.data-field field="Transaction Date" value="{{ \Carbon\Carbon::parse($dispute->transaction->created_at)->timezone('Asia/Manila')->format('m/d/Y') }}" />                
                <x-layout.details.more-details.data-field field="Transaction Amount" value="{{ \Number::currency($dispute->transaction->amount, 'PHP') }}" />
                <x-layout.details.more-details.data-field field="Transaction Reference Number" value="{{ $dispute->transaction->ref_no }}" />
                <x-layout.details.more-details.data-field field="Date Created" value="{{ \Carbon\Carbon::parse($dispute->created_at)->timezone('Asia/Manila')->format('m/d/Y') }}" />
            </div>
        </x-layout.details.more-details.more-section>

        <x-layout.details.more-details.section title="Message">
            <p>{{ $dispute->comment }}</p>
        </x-layout.details.more-details.more-section>

        @if ($dispute->media->count() > 0)
            <x-layout.details.more-details.section title="Attachments">
                <div class="space-y-3">
                    @foreach ($dispute->media as $image)
                        {{-- Attachment 1 --}}
                        <div class="flex">
                            <div class="w-44 h-28">
                                <img src="{{ $this->get_media_url($image) }}" class="w-full h-full object-cover rounded-[4px]" alt="Sofa" />
                            </div>
                            <div class="px-2">
                                <strong>{{ $image->name }}</strong>
                                <p>{{ $image->human_readable_size }}</p>
                            </div>
                        </div>
                    @endforeach
                    
                </div>
            </x-layout.details.more-details.more-section>
        @endif
    </x-layout.details.more-details>
</x-main.content>
