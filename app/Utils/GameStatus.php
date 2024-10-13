<?php

namespace App\Utils;

enum GameStatus: string
{
    case Win = 'win';
    case Lose = 'lose';
    case Draw = 'draw';
    case InProgress = 'in_progress';
    case Pending = 'pending';
}
