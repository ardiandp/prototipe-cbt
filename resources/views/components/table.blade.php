@props([
    'headers' => [],
    'striped' => true,
    'hover' => true,
    'bordered' => false,
    'responsive' => true,
    'empty' => false,
    'emptyMessage' => 'Belum ada data yang tersedia.'
])

<div class="{{ $responsive ? 'table-responsive' : '' }}">
    <table {{ $attributes->merge(['class' => 'table align-middle mb-0 ' . ($striped ? 'table-striped ' : '') . ($hover ? 'table-hover ' : '') . ($bordered ? 'table-bordered ' : '')]) }}>
        @if(!empty($headers))
            <thead class="table-light">
                <tr>
                    @foreach($headers as $header)
                        <th scope="col" class="text-nowrap">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody>
            @if($empty)
                <tr>
                    <td colspan="{{ max(count($headers), 1) }}" class="text-center py-4 text-muted">
                        <div class="empty-state">
                            <span class="fs-1 d-block mb-2">&#128194;</span>
                            <p class="mb-0">{{ $emptyMessage }}</p>
                        </div>
                    </td>
                </tr>
            @else
                {{ $slot }}
            @endif
        </tbody>
    </table>
</div>
