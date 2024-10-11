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

<div class="caveat-font flex flex-col justify-center items-center text-center">
    <div class="font-bold text-4xl mb-3">Share with your friend and play together :)</div>
    <div class="flex justify-center items-center text-3xl space-x-4">
        <button id="copyButton" data-clipboard-text="{{ $endpoint }}" class="underline hover:opacity-60 underline-offset-2 uppercase text-blue-700">Share</button>
    </div>
    <img src="{{ asset('images/qr_code.png') }}" alt="QR Code" class="w-60 h-60 mt-5">
</div>

@script
<script>
    const clipboard = new ClipboardJS('#copyButton');

    clipboard.on('success', function(e) {
        e.trigger.classList.add('text-green-700');
        e.trigger.innerText = 'Copied';

        setTimeout(() => {
            e.trigger.classList.remove('text-green-700');
            e.trigger.innerText = 'Share';
        }, 500);
    });
</script>
@endscript
