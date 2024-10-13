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
            if (!session()->has('player')){
                session()->put('player', [
                    'id' => str()->of(str()->ulid())->lower(),
                    'symbol' => 'X',
                ]);
                $this->gameKey = session('player')['id'];

                Cache::forever($this->gameKey, uniqid('game_', true));
            } else {
                $this->gameKey = session('player')['id'];
            }
        } else {
            if (!session()->has('player')) {
                session()->put('player', [
                    'id' => str()->of(str()->ulid())->lower(),
                    'symbol' => 'O',
                ]);
            }

            $this->gameKey = $gameKey;
        }

        $this->gameId = Cache::get($this->gameKey);
    }

    public function render()
    {
        return view('livewire.pages.play');
    }
}
