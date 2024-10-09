<?php

use App\Events\RestartGame;
use App\Utils\GameStatus;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component {
    public string $gameId;

    #[Reactive]
    public mixed $gameStatus;

    public function restartGame(): void
    {
        RestartGame::dispatch($this->gameId);
    }
}; ?>

<div class="fixed z-10 inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white p-4 rounded-lg shadow-lg">
        <div class="my-3">
            @switch($gameStatus)
                @case(GameStatus::Win)
                    <span>Well Done 🥳</span>
                    @break
                @case(GameStatus::Lose)
                    <span>Game Over 😔</span>
                    @break
                @case(GameStatus::Draw)
                    <span>Game draw 🤷🏼‍</span>
                    @break
            @endswitch
        </div>

        @if($gameStatus === GameStatus::Win || $gameStatus === GameStatus::Draw)
        <button wire:click="restartGame" class="bg-gray-300 p-2 rounded text-gray-800">Restart Game</button>
        @endif
    </div>
</div>
