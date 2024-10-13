<?php

use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component {
    #[Reactive]
    public bool $active;

}; ?>

<div class="flex justify-center m-3 caveat-font">
    <span class="flex justify-center items-center text-3xl font-bold py-0.5">
    @if($active)
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
        </span>
        <span class="ml-2 text-green-700">It's your turn</span>
    @else
        <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
            </span>
        <span class="ml-2 text-amber-700">Waiting for opponent</span>
    @endif
    </span>
</div>
