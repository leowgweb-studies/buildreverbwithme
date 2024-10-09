<?php

use App\Events\MoveMade;
use App\Utils\CheckGame;
use App\Utils\GameStatus;
use App\Utils\PlayCheck;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component {
    public string $gameKey;

    public string $gameId;

    public array $player = [];

    public array $board;

    public bool $active;

    public mixed $gameStatus;

    public function mount(string $gameKey): void
    {
        $this->gameKey = $gameKey;
        $this->gameId = Cache::get($this->gameKey);
        $this->player = session('player');
        $this->active = $this->gameKey == $this->player['id'];
        $this->board = Cache::rememberForever("board_{$this->gameId}", fn() => [
            [null, null, null],
            [null, null, null],
            [null, null, null],
        ]);
        $this->gameStatus = GameStatus::InProgress;
    }

    public function makeMove(int $y, int $x): void
    {
        $payload = [
            'player' => $this->player,
            'position' => [
                'y' => $y,
                'x' => $x,
            ],
        ];

        MoveMade::dispatch($payload, $this->gameId);
    }

    #[On('echo:move-made.{gameId},MoveMade')]
    public function onMoveMade(array $payload): void
    {
        $this->board[$payload['position']['y']][$payload['position']['x']] = $payload['player']['symbol'];
        Cache::forever("board_{$this->gameId}", $this->board);
        $this->active = !$this->active;

        $isCurrentPlayer = $this->player['id'] == $payload['player']['id'];

        $this->gameStatus = CheckGame::run(
            $this->board,
            $isCurrentPlayer
        );
    }

    #[On('echo:restart.{gameId},RestartGame')]
    public function restartGame(): void
    {
        Cache::forget("board_{$this->gameId}");

        $this->board = Cache::rememberForever("board_{$this->gameId}", fn() => [
            [null, null, null],
            [null, null, null],
            [null, null, null],
        ]);

        $this->gameStatus = GameStatus::InProgress;

        $this->active = !$this->active;
    }
}; ?>

<div>
    @if($this->gameKey == $this->player['id'])
        <livewire:components.clipboard :gameKey="$gameKey"/>
    @endif

    <livewire:components.active-player :active="$active"/>

    @if($gameStatus !== GameStatus::InProgress)
        <livewire:components.show-result :gameStatus="$gameStatus" :gameId="$gameId"/>
    @endif

    <div class="grid grid-cols-3 gap-2 bg-white p-4 rounded-lg shadow-lg">
        @foreach($this->board as $y => $row)
            @foreach($row as $x => $col)
                <button :disabled="!$wire.active" wire:click="makeMove({{$y}}, {{$x}})"
                        class="w-24 h-24 bg-gray-200 disabled:cursor-not-allowed flex justify-center items-center text-4xl font-bold cursor-pointer hover:bg-gray-300 transition-colors duration-300 rounded">
                    <span>{{ $this->board[$y][$x] }}</span>
                </button>
            @endforeach
        @endforeach
    </div>
</div>
