@props(['title', 'subtitle', 'description', 'badges' => []])

<div class="flex flex-col gap-1">
    <div>
        <div class="font-medium text-gray-950 dark:text-white">
            {{ $title }}
        </div>

        @if ($subtitle)
            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ $subtitle }}
            </div>
        @endif
    </div>

    @if ($description)
        <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ $description }}
        </div>
    @endif

    @if (count($badges))
        <div class="flex gap-1">
            @foreach ($badges as $badge)
                <x-filament::badge :color="$badge['color']">
                    {{ $badge['label'] }}
                </x-filament::badge>
            @endforeach
        </div>
    @endif
</div>
