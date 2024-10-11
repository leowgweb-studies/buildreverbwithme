<?php

use App\Events\RestartGame;
use App\Utils\GameStatus;
use Illuminate\Support\Arr;
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

    public function getExpression(): string
    {
        $win = [
            '<span>:D</span> Well done!',
            '<span>\o/</span> Congratulations!',
            '<span>:)</span> Good job!',
            '<span>^_^</span> Way to go!',
            '<span>:-)</span> Nice work!'
        ];

        $lose = [
            '<span>:/</span> Better luck next time.',
            '<span>|-O</span> Good game.',
            '<span>:)</span> Nice try.',
            '<span>:o)</span> Don\'t worry about it.',
            '<span>;)</span> You\'ll get it next time.'
        ];

        $draw = [
            '<span>:|</span> It\'s a tie!',
            '<span>:-||</span> We\'re evenly matched!',
            '<span>-_-</span> Nobody won, nobody lost.',
            '<span>=|</span> Back to square one.',
            '<span>:/</span> Let\'s call it a draw.'
        ];

        return match ($this->gameStatus) {
            GameStatus::Win => '<p class="text-green-700 text-6xl">You win!</p>' . '<p class="text-gray-900 text-3xl">' . Arr::random($win) . '</p>',
            GameStatus::Lose => '<p class="text-red-700 text-6xl">You lose!</p>' . '<p class="text-gray-900 text-3xl">' . Arr::random($lose) . '</p>',
            GameStatus::Draw => '<p class="text-gray-700 text-6xl">Draw!</p>' . '<p class="text-gray-900 text-3xl">' . Arr::random($draw) . '</p>',
            default => '',
        };
    }
}; ?>

<div class="caveat-font">
    <div class="flex flex-col text-center space-y-5">
        <div>{!! $this->getExpression() !!}</div>

        @if($gameStatus === GameStatus::Win || $gameStatus === GameStatus::Draw)
            <button wire:click="restartGame" class="font-bold text-blue-700 underline underline-offset-2 uppercase hover:opacity-80 text-lg">Restart Game</button>
        @endif
    </div>
</div>
