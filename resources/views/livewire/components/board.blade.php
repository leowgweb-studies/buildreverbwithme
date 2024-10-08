<?php

use App\Events\MoveMade;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public string $gameId;

    public array $player = [];

    public array $board;

    public function mount(string $gameKey): void
    {
        $this->gameId = Cache::get($gameKey);
        $this->player = session('player');
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
    }
}; ?>

<div x-data="{
        localBoard: $wire.entangle('board'),
    }" class="grid grid-cols-3 gap-2 bg-white p-4 rounded-lg shadow-lg">
    @foreach($this->board as $y => $row)
        @foreach($row as $x => $col)
            <div x-on:click="$wire.makeMove({{$y}}, {{$x}})"
                 class="w-24 h-24 bg-gray-200 flex justify-center items-center text-4xl font-bold cursor-pointer hover:bg-gray-300 transition-colors duration-300">
                <span x-text="localBoard[{{$y}}][{{$x}}]"></span>
            </div>
        @endforeach
    @endforeach
</div>
