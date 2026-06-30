@props(['items' => []])

<nav aria-label="breadcrumb" class="flex items-center gap-2 text-sm text-ink-2 mb-2">
    <a href="{{ route('ninebox.dashboard') }}" class="hover:text-primary transition-colors">
        {{ __('Inicio') }}
    </a>

    @foreach ($items as $item)
        <span class="text-ink-3">›</span>
        @if (!empty($item['url']) && !$loop->last)
            <a href="{{ $item['url'] }}" class="hover:text-primary transition-colors">
                {{ $item['label'] }}
            </a>
        @else
            <span class="text-ink font-medium">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
