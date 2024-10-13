<?php

use App\Events\FinishGame;
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

    public string $active;

    public mixed $gameStatus;

    public string $gameStatusCacheKey;

    public function mount(string $gameKey): void
    {
        $this->gameKey = $gameKey;
        $this->gameId = Cache::get($this->gameKey);
        $this->player = session('player');
        $this->board = Cache::rememberForever("board_{$this->gameId}", fn() => [
            [null, null, null],
            [null, null, null],
            [null, null, null],
        ]);

        $this->gameStatusCacheKey = "status_{$this->player['id']}_{$this->gameId}";

        $this->gameStatus = Cache::get($this->gameStatusCacheKey, GameStatus::Pending);

        $this->active = Cache::rememberForever("active_player_{$this->gameId}", fn() => $this->player['id']);

        if (
            $this->gameKey != $this->player['id']
            && $this->gameStatus == GameStatus::Pending
        ) {
            $this->gameStatus = Cache::rememberForever($this->gameStatusCacheKey, fn() => GameStatus::InProgress);
            broadcast(new StartMatch($this->gameId))->toOthers();
        }
    }

    #[On('echo:start-match.{gameId},StartMatch')]
    public function onStartMatch(): void
    {
        $this->gameStatus = Cache::rememberForever($this->gameStatusCacheKey, fn() => GameStatus::InProgress);
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

        $this->board[$payload['position']['y']][$payload['position']['x']] = $payload['player']['symbol'];

        Cache::forever("board_{$this->gameId}", $this->board);

        MoveMade::dispatch($payload, $this->gameId);
    }

    #[On('echo:move-made.{gameId},MoveMade')]
    public function onMoveMade(array $payload): void
    {
        $this->board = Cache::get("board_{$this->gameId}");

        $isCurrentPlayer = $this->player['id'] == $payload['player']['id'];

        if (!$isCurrentPlayer) {
            Cache::forever("active_player_{$this->gameId}", $this->player['id']);
        }

        $this->dispatch('change-turn');

        $this->gameStatus = CheckGame::run(
            $this->board,
            $isCurrentPlayer
        );

        Cache::forever($this->gameStatusCacheKey, $this->gameStatus);
    }

    #[On('echo:restart.{gameId},RestartGame')]
    public function restartGame(): void
    {
        $gameStatus = Cache::get($this->gameStatusCacheKey);

        Cache::forget($this->gameStatusCacheKey);

        $this->board = Cache::rememberForever("board_{$this->gameId}", fn() => [
            [null, null, null],
            [null, null, null],
            [null, null, null],
        ]);

        $this->gameStatus = Cache::rememberForever($this->gameStatusCacheKey, fn() => GameStatus::InProgress);

        if ($gameStatus === GameStatus::Win) {
            Cache::forever("active_player_{$this->gameId}", $this->player['id']);
        }

        $this->player['symbol'] = $this->player['symbol'] == 'X' ? 'O' : 'X';

        session()->put('player', $this->player);

        $this->dispatch('change-turn');
    }

    #[On('echo:finish.{gameId},FinishGame')]
    public function finishGame(): void
    {
        Cache::forget($this->gameStatusCacheKey);
        Cache::forget($this->gameKey);

        session()->forget('player');

        redirect(route('game.home'));
    }

    #[On('change-turn')]
    public function activePlayer(): void
    {
        $this->active = Cache::get("active_player_{$this->gameId}");
    }

    #[On('close-game')]
    public function closeGame(): void
    {
        Cache::forget("game_{$this->gameId}");
        Cache::forget("board_{$this->gameId}");
        Cache::forget("active_player_{$this->gameId}");

        FinishGame::dispatch($this->gameId);
    }

    #[Computed]
    public function showResult(): bool
    {
        return $this->gameStatus !== (GameStatus::InProgress || GameStatus::Pending);
    }

}; ?>

<div>
    <button wire:click="$dispatch('close-game')" class="absolute top-5 right-5 p-3 text-xl caveat-font text-red-700/60 hover:opacity-70">( x ) Close Game
    </button>

    @if($this->gameStatus === GameStatus::Pending)
        <livewire:components.clipboard :gameKey="$gameKey"/>
    @endif

    @if($this->showResult)
        <livewire:components.show-result :gameStatus="$gameStatus" :gameId="$gameId"/>
    @endif

    @if($this->gameStatus === GameStatus::InProgress)
        <livewire:components.active-player :active="$active == $player['id']"/>

        <div class="w-full md:w-3/5 lg:w-1/2 xl:w-5/12 p-6 lg:p-4 mx-auto grid grid-cols-3 place-content-center">
            @foreach($this->board as $y => $row)
                @foreach($row as $x => $col)
                    <button :disabled="$wire.active != $wire.player['id']" wire:click="makeMove({{$y}}, {{$x}})"
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
