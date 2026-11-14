@props([
    'title' => null,
    'type' => null,
    'outline' => false,
    'headerClass' => '',
    'bodyClass' => '',
    'footerClass' => ''
])

<div {{ $attributes->merge(['class' => 'card shadow-sm ' . ($type ? ($outline ? 'card-outline card-' . $type : 'text-bg-' . $type) : '')]) }}>
    @if($title || isset($tools) || isset($header))
        <div class="card-header d-flex justify-content-between align-items-center {{ $headerClass }}">
            @if(isset($header))
                {{ $header }}
            @else
                <h3 class="card-title fs-5 mb-0 fw-semibold">{{ $title }}</h3>
            @endif

            @if(isset($tools))
                <div class="card-tools ms-auto">
                    {{ $tools }}
                </div>
            @endif
        </div>
    @endif

    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="card-footer {{ $footerClass }}">
            {{ $footer }}
        </div>
    @endif
</div>

