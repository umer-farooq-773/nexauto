<div class="card border-0 {{ $card }} rounded-2">
    @if ($cardHeader != null || isset($headerButton) || isset($headerCustom))
    <div class="card-actions float-end">
            @if (isset($headerButton))
            <a href="javascript::void(0)" class="me-1" onclick="location.reload()">
                <i class="align-middle" data-feather="refresh-cw"></i>
            </a>
            @endif

            @if (isset($headerCustom))
            {{ $headerCustom }}
            @endif
        </div>
        
        <h5 class="card-title  fs  fw-semibold">{{ isset($cardHeader) ? $cardHeader : '' }}</h5>
    @endif

    <div class="{{ $cardBody }} p-0">
        {{ $slot }}
    </div>
</div>