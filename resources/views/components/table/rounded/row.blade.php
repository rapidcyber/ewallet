<tr {{ $attributes->merge(['class' => "odd:bg-transparent even:bg-white break-words overflow-hidden"]) }}>
    {{ $slot }} 
</tr>