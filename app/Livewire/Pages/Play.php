<?php

namespace App\Livewire\Pages;

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class Play extends Component
{
    public string $gameKey;

    public ?string $gameId = null;

    public function mount(string $gameKey = null): void
    {
        if (!$gameKey) {
            if (!session()->has('host_user')){
                session()->put('host_user', str()->of(str()->ulid())->lower());
                $this->gameKey = session('host_user');

                Cache::forever($this->gameKey, uniqid('game_', true));
            } else {
                $this->gameKey = session('host_user');
            }
        } else {
            $this->gameKey = $gameKey;
        }

        $this->gameId = Cache::get($this->gameKey);
    }

    public function render()
    {
        return view('livewire.pages.play');
    }
}
