@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name', 'Laravel')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-white ring-1 ring-black/5 dark:ring-white/10">
            <img src="{{ asset('images/jobnest-mark.png') }}" alt="" class="size-7 object-contain" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="config('app.name', 'Laravel')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-white ring-1 ring-black/5 dark:ring-white/10">
            <img src="{{ asset('images/jobnest-mark.png') }}" alt="" class="size-7 object-contain" />
        </x-slot>
    </flux:brand>
@endif
