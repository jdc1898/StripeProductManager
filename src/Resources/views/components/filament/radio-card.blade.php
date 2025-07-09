@props(['title', 'description', 'selected' => false])

<div @class([
    'flex flex-col items-start w-full transition-all duration-200',
    'ring-2 ring-primary-500' => $selected,
])>
    <div class="flex flex-col w-full border-primary-500 ring-2 ring-primary-500 rounded-lg p-2">
        <div class="flex items-center">
            <span class="font-semibold text-primary-600">
                {{ $title }}
            </span>
        </div>
        <span class="text-gray-600 text-xs">
            {{ $description }}
        </span>
    </div>
</div>
