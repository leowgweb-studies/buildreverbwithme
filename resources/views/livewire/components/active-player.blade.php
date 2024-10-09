<?php

use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component {
    #[Reactive]
    public bool $active;

}; ?>

<div class="text-center m-3">
    @if($active)
        <span>It's your turn</span>
    @else
        <span>Waiting for opponent</span>
    @endif
</div>
