<?php

namespace App\Utils;

class CheckGame
{
    public static function run(array $board, bool  $isCurrentPlayer): GameStatus
    {
        if (self::checkWin($board)) {
            return $isCurrentPlayer
                ? GameStatus::Win
                : GameStatus::Lose;
        }

        if (self::checkDraw($board)) {
            return GameStatus::Draw;
        }

        return GameStatus::InProgress;
    }

    private static function checkWin(array $board): bool
    {
        $winningCombinations = [
            [[0, 0], [0, 1], [0, 2]],
            [[1, 0], [1, 1], [1, 2]],
            [[2, 0], [2, 1], [2, 2]],
            [[0, 0], [1, 0], [2, 0]],
            [[0, 1], [1, 1], [2, 1]],
            [[0, 2], [1, 2], [2, 2]],
            [[0, 0], [1, 1], [2, 2]],
            [[0, 2], [1, 1], [2, 0]],
        ];

        foreach ($winningCombinations as $combination) {
            $values = [];
            foreach ($combination as $cell) {
                $values[] = $board[$cell[0]][$cell[1]];
            }
            if (count(array_unique($values)) === 1 && $values[0] !== null) {
                return true;
            }
        }

        return false;
    }

    private static function checkDraw(array $board): bool
    {
        foreach ($board as $row) {
            if (in_array(null, $row)) {
                return false;
            }
        }

        return true;
    }
}
