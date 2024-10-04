<?php

use App\Events\SwitchFlipped;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public bool $toggleSwitch = false;

    public function mount(): void
    {
        $this->toggleSwitch = Cache::get('toggleSwitch', false);
    }

    public function flipSwitch(): void
    {
        Cache::forever('toggleSwitch', $this->toggleSwitch);
        broadcast(new SwitchFlipped($this->toggleSwitch))->toOthers();
    }

    #[On('echo:switch,SwitchFlipped')]
    public function registerSwitchFlipped(array $payload): void
    {
        $this->toggleSwitch = $payload['toggleSwitch'];
        Cache::forever('toggleSwitch', $this->toggleSwitch);
    }
}; ?>

<div x-data="{
    localToggle: @entangle('toggleSwitch'),
}">
    <label for="toggleSwitch" class="inline-flex items-center cursor-pointer">
        <input type="checkbox" id="toggleSwitch" class="sr-only peer" x-model="localToggle"
               x-on:change="$wire.flipSwitch()"/>
        <div
            class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
    </label>
</div>
