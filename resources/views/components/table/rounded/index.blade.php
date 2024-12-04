<table {{ $attributes->merge(['class' => "w-full space-y-3 rounded-lg table-auto"]) }}>
    <thead>
        <tr {{ $table_header->attributes->merge(['class' => 'bg-white']) }}>
           {{ $table_header }}
        </tr>
    </thead>
    <tbody {{ $table_data->attributes }}>
        {{ $table_data }}
    </tbody>
</table>