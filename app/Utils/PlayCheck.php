<?php

namespace App\Utils;

class PlayCheck
{
    public static function win(array $board): bool
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

    public static function draw(array $board): bool
    {
        foreach ($board as $row) {
            if (in_array(null, $row)) {
                return false;
            }
        }

        return true;
    }
}
