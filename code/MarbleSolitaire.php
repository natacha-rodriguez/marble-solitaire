<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of MarbleSolitaire
 *
 * @author Naty
 */
class MarbleSolitaire {

    const FULL_CELL = 1;
    const EMPTY_CELL = 2;
    const SIZE = 7; //size of the sqare matrix to represent the board
    const MIDDLE_INDEX = 3;
    const X_AXIS = 'i';
    const Y_AXIS = 'j';
    const ALLOWED_JUMP = 2;
    const MIDDLE_JUMP = 1;
    const MOVES = 32;

    public static function getAllSetCells($tempBoard) {
        $setCells = array();
        foreach ($tempBoard as $i => $row) {
            foreach ($row as $j => $cellValue) {
                if ($cellValue == self::FULL_CELL) {
                    $setCells[] = array(self::X_AXIS => $i, self::Y_AXIS => $j);
                }
            }
        }
        return $setCells;
    }

    public static function getStartingBoard($isStarting = true) {
        $fullCell = ($isStarting) ? (self::FULL_CELL) : (self::EMPTY_CELL);
        $emptyCell = ($isStarting) ? (self::EMPTY_CELL) : (self::FULL_CELL);
        for ($i = 0; $i < self::SIZE; $i++) {
            switch ($i) {
                //rows from the top and bottom of the cross
                case 0:
                case 1:
                case 5:
                case 6:
                    for ($j = 2; $j < 5; $j++) {
                        $board[$i][$j] = $fullCell;
                    }
                    break;
                //rows from the center of the cross
                case 2:
                case 3: //the center of the board will be cleaned up later for efficiency purposes
                case 4:
                    for ($j = 0; $j < 7; $j++) {
                        $board[$i][$j] = $fullCell;
                    }
                    break;
                default:
                    break;
            }
        }
        $board[self::MIDDLE_INDEX][self::MIDDLE_INDEX] = $emptyCell; //empty center of the board
        return $board;
    }

    /**
     * Makes a move from one cell to the other, returning an updated copy of the board.
     * @param <type> $fromCell
     * @param <type> $toCell
     * @param <type> $board
     * @return <array> returns a board reflecting the move done
     */
    public static function jump($fromCell, $toCell, $board) {

        $toI = $toCell[self::X_AXIS];
        $toJ = $toCell[self::Y_AXIS];
        $fromI = $fromCell[self::X_AXIS];
        $fromJ = $fromCell[self::Y_AXIS];

        //if fromCell is not set or toCell is not free, then it's an invalid move
        if ($board[$toI][$toJ] != self::EMPTY_CELL || $board[$fromI][$fromJ] != self::FULL_CELL) {
            return null;
        }

        $Xmovement = $toI - $fromI;
        $Ymovement = $toJ - $fromJ;

        //if there is movement only in one axis, and it is the value of the allowed jump
        if (!(($Xmovement == 0 || abs($Ymovement) == self::ALLOWED_JUMP)
                || (abs($Xmovement) == self::ALLOWED_JUMP || $Ymovement == 0))) {
            return null;
        }

        $middleCellValue = self::EMPTY_CELL;

        //middle cell shouldn't be already set to the middleCellValue
        if ($board[$fromI + ($Xmovement / 2)][$fromJ + ($Ymovement / 2)] == $middleCellValue) {
            return null;
        }

        //if everything is ok, then make the move
        $board[$fromI][$fromJ] = self::EMPTY_CELL;
        $board[$toI][$toJ] = self::FULL_CELL;
        $board[$fromI + ($Xmovement / 2)][$fromJ + ($Ymovement / 2)] = $middleCellValue;

        return $board;
    }

    //checks whether the current board status is a starting board or not
    public static function isSolutionBoard($board, $checkForStarting = true) {
        $isStart = false;

        $valueForMiddle = ($checkForStarting) ? (self::EMPTY_CELL) : (self::FULL_CELL);

        if ($board[self::MIDDLE_INDEX][self::MIDDLE_INDEX] == $valueForMiddle) {
            $isStart = true;
            for ($i = 0; $i < self::SIZE; $i++) {
                for ($j = 0; $j < self::SIZE; $j++) {
                    if ($board[$i][$j] == $valueForMiddle && !($i == self::MIDDLE_INDEX && $j == self::MIDDLE_INDEX)) {
                        $isStart = false;
                    }
                }
            }
        }
        return $isStart;
    }

    public static function getPossibleMoves($board, $cell, $isReverse = true) {
        $cells = array();
        $x = $cell[self::X_AXIS];
        $y = $cell[self::Y_AXIS];

        //only one axis can be moved, thus, only one loop
        //just need to see the possitive and negative allowed jump, not when it's 0
        for ($i = (-self::ALLOWED_JUMP); $i < (self::ALLOWED_JUMP + 1); $i += ( 2 * self::ALLOWED_JUMP)) {

            // x axis move
            if (($x + $i) >= 0) {
                $tempCell = array(self::X_AXIS => $x + $i, self::Y_AXIS => $y);
                //if it's a valid place for a jump
                if (self::jump($cell, $tempCell, $board, $isReverse) != null) {
                    //then add it as a possible origin
                    $cells[] = $tempCell;
                }
            }
            //y axis move
            if (($y + $i) >= 0) {
                $tempCell = array(self::X_AXIS => $x, self::Y_AXIS => $y + $i);
                if (self::jump($cell, $tempCell, $board, $isReverse) != null) {
                    $cells[] = $tempCell;
                }
            }
        }
        return $cells;
    }

}

?>
