<div class="px-3 py-3.5">
    @if (empty($getState()) == false)
        @foreach ($getState() as $variable => $value)
            {{-- <div class="flex justify-between"> --}}
            @if (is_array($value))
                @foreach ($value as $key => $val )
                <p class="ml-2">{{ $key . ' => ' . $val }}</p>
                @endforeach
            @else
                <p>{{ $variable . ' => ' . $value }}</p>
                {{-- </div> --}}
            @endif
        @endforeach
    @else
        <p>Nothing to show</p>
    @endif
</div>
