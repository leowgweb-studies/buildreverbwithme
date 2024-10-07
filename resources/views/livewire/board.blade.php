<?php

use App\Events\StartGame;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public array $users = [];

    public string $gameId;

    public function mount(): void
    {
        $this->gameId = uniqid('game_', true);
    }

    public function startGame(): void
    {
        if (!Session::has('user_id')) {
            $user = uniqid('user_', true);
            Session::put('user_id', $user);
        } else {
            $user = Session::get('user_id');
        }

        Cache::put($this->gameId, $user . '_' . $this->gameId);

        broadcast(new StartGame($this->gameId, $user));
    }

    #[On('echo:game.{gameId},StartGame')]
    public function onStartGame(array $payload): void
    {
        $this->users[] = $payload['user'];
    }
}; ?>

<div x-data="{
        users: $wire.entangle('users'),
    }">
    <button wire:click="startGame()" class="p-5 rounded bg-amber-50 text-amber-700">Start Game</button>
    <ul>
        @foreach ($users as $user)
            <li> {{ $user }} </li>
        @endforeach
    </ul>
</div>
