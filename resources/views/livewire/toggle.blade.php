<?php

use App\Events\MouseMoved;
use App\Events\MoveMade;
use App\Events\SwitchFlipped;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public bool $toggleSwitch = false;

    public array $board = [
        [null, null, null],
        [null, null, null],
        [null, null, null],
    ];

    public array $gridPosition = [];

    public array $activePlayers = [];

    #[Locked]
    public string $userId;

    #[Locked]
    public int $activeUsersCount = 0;

    #[Locked]
    public array $mousePositions = [];

    #[Locked]
    public array $userColors = [];

    public function updateActiveUsersCount(): void
    {
        $this->activeUsersCount = count($this->mousePositions) + 1;
    }

    public function generateRandomColor(): string
    {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    public function mount(): void
    {
        if (!Session::has('user_id')) {
            $this->userId = uniqid('user_', true);
            Session::put('user_id', $this->userId);
        } else {
            $this->userId = Session::get('user_id');
        }

        $this->board = Cache::get('board', $this->board);
        $this->activePlayers[$this->userId] = $this->generateRandomColor();

        $this->toggleSwitch = Cache::get('toggleSwitch', false);
        $this->userColors[$this->userId] = $this->generateRandomColor();
        $this->updateActiveUsersCount();
    }

    public function makeMove(int $y, int $x): void
    {
        $payload = [
            'userId' => $this->userColors[$this->userId],
            'position' => [
                'y' => $y,
                'x' => $x,
            ],
        ];

        Cache::forever('board', $this->board);
        broadcast(new MoveMade($payload));
    }

    #[On('echo:move-made,MoveMade')]
    public function registerMoveMade(array $payload): void
    {
        //dd($payload);
        $this->board[$payload['position']['y']][$payload['position']['x']] = $payload['userId'];
        Cache::forever('board', $this->board);
    }

    public function flipSwitch(): void
    {
        Cache::forever('toggleSwitch', $this->toggleSwitch);
        broadcast(new SwitchFlipped($this->toggleSwitch))->toOthers();
    }

    #[On('echo:switch,SwitchFlipped')]
    public function registerSwitchFlipped(array $payload): void
    {
        $this->toggleSwitch = $payload['toggleSwitch'];
        Cache::forever('toggleSwitch', $this->toggleSwitch);
    }

    #[On('echo:mouse-movement,MouseMoved')]
    public function registerMouseMoved(array $payload): void
    {
        if ($payload['position'] !== null) {
            $this->mousePositions[$payload['userId']] = $payload['position'];
            if (!isset($this->userColors[$payload['userId']])) {
                $this->userColors[$payload['userId']] = $this->generateRandomColor();
            }
        } else {
            unset($this->mousePositions[$payload['userId']]);
        }

        $this->updateActiveUsersCount();
    }

    public function moveMouse(array $position): void
    {
        $payload = [
            'userId' => $this->userId,
            'position' => $position,
            'color' => $this->userColors[$this->userId],
        ];

        broadcast(new MouseMoved($payload))->toOthers();
    }

    public function setInactive(): void
    {
        unset($this->mousePositions[$this->userId]);
        $this->updateActiveUsersCount();
        broadcast(
            new MouseMoved([
                'userId' => $this->userId,
                'position' => null,
                'color' => null,
            ])
        )->toOthers();
    }
}; ?>

<div x-data="{
    localToggle: @entangle('toggleSwitch'),
    cursors: @entangle('mousePositions'),
    smoothCursors: {},
    cursorSpeed: 0.1,
    init() {
        this.$watch('cursors', (value) => {
            this.updateSmoothCursors(value);
        });
        this.animateCursors();
    },
    updateSmoothCursors(newCursors) {
        for (let userId in this.smoothCursors) {
            if (!newCursors[userId]) {
                delete this.smoothCursors[userId];
            }
        }
        for (let userId in newCursors) {
            if (!this.smoothCursors[userId] && newCursors[userId]) {
                this.smoothCursors[userId] = { ...newCursors[userId], active: true };
            } else if (this.smoothCursors[userId] && newCursors[userId]) {
                this.smoothCursors[userId].active = true;
            }
        }
    },
    animateCursors() {
        for (let userId in this.smoothCursors) {
            if (this.cursors[userId] && this.smoothCursors[userId].active) {
                let target = this.cursors[userId];
                let current = this.smoothCursors[userId];

                current.x += (target.x - current.x) * this.cursorSpeed;
                current.y += (target.y - current.y) * this.cursorSpeed;
            }
        }
        requestAnimationFrame(() => this.animateCursors());
    }
}">
    <div>
        <label for="toggleSwitch" class="inline-flex items-center cursor-pointer">
            <input type="checkbox" id="toggleSwitch" class="sr-only peer" x-model="localToggle"
                   x-on:change="$wire.flipSwitch()"/>
            <div
                class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
        </label>
    </div>
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
    <template x-for="(position, userId) in smoothCursors" :key="userId">
        <div class="cursor-dot" x-show="position.active"
             :style="`left: calc(50% + ${position.x * 50}%);
                      top: calc(50% + ${position.y * 50}%);
                      background-color: ${$wire.userColors[userId] || '#000000'};`"
        >
        </div>
    </template>
    <div class="fixed bottom-0 right-0 p-4 text-white bg-black bg-opacity-50 rounded-tl-lg">
        Active Users: {{ $activeUsersCount }}
    </div>
</div>
