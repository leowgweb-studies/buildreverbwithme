<?php

use Livewire\Volt\Component;

new class extends Component {
    public string $endpoint;

    public function mount(string $gameKey): void {
        $this->endpoint = route('game.play', $gameKey);
    }

    public function copyToClipboard(): void {

    }
}; ?>

<div class="caveat-font text-center">
    <span class="font-bold text-3xl mb-3">Share the link below with your friend and play together :)</span>
    <div class="flex justify-center items-center text-2xl">
        <span class="truncate text-red-600">{{ $endpoint }}</span>
        <button id="copyButton" data-clipboard-text="{{ $endpoint }}" class="underline hover:opacity-60 underline-offset-1 uppercase text-blue-700">Copy</button>
    </div>
</div>

@script
<script>
    const clipboard = new ClipboardJS('#copyButton');

    clipboard.on('success', function(e) {
        e.trigger.classList.add('text-green-700');
        e.trigger.innerText = 'Copied';

        setTimeout(() => {
            e.trigger.classList.remove('text-green-700');
            e.trigger.innerText = 'Copy';
        }, 500);
    });
</script>
@endscript
