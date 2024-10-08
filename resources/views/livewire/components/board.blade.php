<?php

use App\Events\MoveMade;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public string $gameId;

    public array $player = [];

    public array $board;

    public bool $active;

    public function mount(string $gameKey): void
    {
        $this->gameId = Cache::get($gameKey);
        $this->player = session('player');
        $this->active = $gameKey == $this->player['id'];
        $this->board = Cache::rememberForever("board_{$this->gameId}", fn() => [
            [null, null, null],
            [null, null, null],
            [null, null, null],
        ]);
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
    }
}; ?>

<div>
    <div class="text-center m-3">
        @if($active)
            <span>You Turn</span>
        @else
            <span>Waiting for opponent</span>
        @endif
    </div>
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
