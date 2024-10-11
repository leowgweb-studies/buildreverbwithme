<?php

use App\Events\MoveMade;
use App\Events\StartMatch;
use App\Utils\CheckGame;
use App\Utils\GameStatus;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
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

    #[Computed]
    public function showResult(): bool
    {
        return $this->gameStatus !== GameStatus::InProgress;
    }
}; ?>

<div>
    @if(!$this->startMatch)
        <livewire:components.clipboard :gameKey="$gameKey"/>
    @endif

    @if($this->showResult)
        <livewire:components.show-result :gameStatus="$gameStatus" :gameId="$gameId"/>
    @endif

    @if($this->startMatch && !$this->showResult)
        <livewire:components.active-player :active="$active"/>

        <div class="w-full md:w-3/5 lg:w-1/2 xl:w-5/12 p-6 lg:p-4 mx-auto grid grid-cols-3 place-content-center">
            @foreach($this->board as $y => $row)
                @foreach($row as $x => $col)
                    <button :disabled="!$wire.active" wire:click="makeMove({{$y}}, {{$x}})"
                            class="w-full h-24 md:h-32 lg:h-36 border-gray-800 disabled:cursor-not-allowed flex justify-center items-center text-4xl font-bold cursor-pointer hover:bg-gray-500/20 transition-colors duration-300"
                            :class="{
                               'border-r-2': {{ $x }} < 3 - 1,
                               'border-b-2': {{ $y }} < 3 - 1,
                            }"
                    >
                        <span>{{ $this->board[$y][$x] }}</span>
                    </button>
                @endforeach
            @endforeach
        </div>
    @endif
</div>
