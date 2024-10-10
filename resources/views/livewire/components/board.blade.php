<?php

use App\Events\MoveMade;
use App\Events\StartMatch;
use App\Utils\CheckGame;
use App\Utils\GameStatus;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public string $gameKey;

    public string $gameId;

    public array $player = [];

    public array $board;

    public bool $active;

    public mixed $gameStatus;

    public bool $startMatch = false;

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

        if ($this->gameKey != $this->player['id']) {
            $this->startMatch = true;
            broadcast(new StartMatch($this->gameId))->toOthers();
        }
    }

    #[On('echo:start-match.{gameId},StartMatch')]
    public function onStartMatch(): void
    {
        $this->startMatch = true;
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
    @if(!$this->startMatch)
        <livewire:components.clipboard :gameKey="$gameKey"/>
    @endif

    <livewire:components.active-player :active="$active"/>

    @if($gameStatus !== GameStatus::InProgress)
        <livewire:components.show-result :gameStatus="$gameStatus" :gameId="$gameId"/>
    @endif

    <div class="w-full grid grid-cols-3 place-items-center divide-x-reverse divide-y-reverse divide-gray-700">
        @foreach($this->board as $y => $row)
            @foreach($row as $x => $col)
                <button :disabled="!$wire.active" wire:click="makeMove({{$y}}, {{$x}})"
                        class="w-full h-24 bg-transparent border-black disabled:cursor-not-allowed flex justify-center items-center text-4xl font-bold cursor-pointer hover:bg-gray-500/20 transition-colors duration-300"
                        :class="{
                           'border-r-4': {{ $x }} < 3 - 1,
                           'border-b-4': {{ $y }} < 3 - 1,
                        }"
                >
                    <span>{{ $this->board[$y][$x] }}</span>
                </button>
            @endforeach
        @endforeach
    </div>
</div>
