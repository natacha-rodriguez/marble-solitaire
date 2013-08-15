<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once 'MarbleSolitaire.php';

/**
 * Description of MarbleSolitaireGeneticSolution
 *
 * @author Naty
 */
class MarbleSolitaireGeneticSolution {
    //indexes for sorted population structure
    const WEIGHTS_INDEX = 0;
    const MOVES_INDEX = 1;
    const ORIGINAL_INDEX = 2;

    //indexes for sorted moves
    const INDIVIDUAL_MOVE_INDEX = 0;
    const BOARD_INDEX = 1;

    //settings for the algorithm
    const MAX_TRIES = 30;
    const POP_SIZE = 21;
    const WEIGHT_COUNT = 7;
    const MIN_WEIGHT = 1;
    const MAX_WEIGHT = 10;
    const CHANGE_OF_CRITERIA = 10;
    const IDEAL_FITNESS = 1;


    //cell classes
    const CLASS_A = 0;
    const CLASS_B = 1;
    const CLASS_C = 2;
    const CLASS_D = 3;
    const CLASS_E = 4;
    const CLASS_F = 5;
    const CLASS_G = 6;

    /**
     * takes two individuals and randombly generates a new one by
     * using the weights from any of them for each class
     * @param <type> $selection
     * @return <type>
     */
    public function combineIndividuals($selection) {
        $ind1 = $selection[0][self::WEIGHTS_INDEX];
        $ind2 = $selection[1][self::WEIGHTS_INDEX];
        $result = array();
        //randomly choose the weight of one of the individuals for each class
        for ($i = 0; $i < self::WEIGHT_COUNT; $i++) {
            $rand = mt_rand();
            if ($rand % 2 == 0) {
                $result[$i] = $ind1[$i];
            } else {
                $result[$i] = $ind2[$i];
            }
        }
        unset($ind1);
        unset($ind2);
        return $result;
    }

    /**
     * chooses the two individuals with better fitness
     * @param <type> $sortedPopulation
     * @return <type>
     */
    public function chooseIndividuals($sortedPopulation) {
        $selection = array();
        foreach ($sortedPopulation as $fitness => $group) {
            if (count($selection) < 2) {
                foreach ($group as $individual) {
                    if (count($selection) < 2) {
                        $selection[] = $individual;
                    } else {
                        break;
                    }
                }
            }
        }
        return $selection;
    }

    /**
     * looks all possible ways until finding a solution
     * @param <type> $individual
     * @param <type> $board
     * @return <type>
     */
    public function getBestPath($individual, $board) {
        if ($board == null) {
            return null;
        }
        $moves = array();
        $setCells = MarbleSolitaire::getAllSetCells($board);
        $fitness = count($setCells);
        $sortedMoves = $this->sortMoves($individual, $board, $setCells);
        $solution = array($fitness, $moves);

        foreach ($sortedMoves as $group) {
            foreach ($group as $step) {
                $move = $step[self::INDIVIDUAL_MOVE_INDEX];
                $tempBoard = $step[self::BOARD_INDEX];
                $tempSolution = array();
                if (MarbleSolitaire::isSolutionBoard($tempBoard, false)) {
                    $tempSolution = array(self::IDEAL_FITNESS, array($move));
                    $fitness = self::IDEAL_FITNESS;
                    $solution = $tempSolution;
                    break;
                } else { //solution not found yet
                    list($tempFitness, $moves) = $this->getBestPath($individual, $tempBoard);

                    $moves[] = $move;
                    $tempSolution = array($tempFitness, $moves);

                    if ($tempFitness != null && $tempFitness < $fitness) { //if this path is better
                        $fitness = $tempFitness;
                        $solution = $tempSolution;
                    }
                }
            }
        }

        unset($moves);
        unset($setCells);
        unset($fitness);
        unset($sortedMoves);

        return $solution;
    }

    /**
     * determines the class of a given cell. All cells of the same class
     * have the same weight.
     * @param <type> $x
     * @param <type> $y
     * @return <type>
     */
    public function determineCellClass($x, $y) {
        $class = null;
        $cell = array(MarbleSolitaire::X_AXIS => $x,
            MarbleSolitaire::Y_AXIS => $y);
        $xDiff = abs($x - MarbleSolitaire::MIDDLE_INDEX);
        $yDiff = abs($y - MarbleSolitaire::MIDDLE_INDEX);

        $class = self::CLASS_A; //this is the default class

        if (($xDiff == MarbleSolitaire::MIDDLE_INDEX && $yDiff == 0)
                || ($yDiff == MarbleSolitaire::MIDDLE_INDEX && $xDiff == 0)) {
            $class = self::CLASS_B;
        } else if (($xDiff == (MarbleSolitaire::MIDDLE_INDEX - 1) && $yDiff != 0)
                || ($yDiff == (MarbleSolitaire::MIDDLE_INDEX - 1) && $xDiff != 0)) {
            $class = self::CLASS_C;
        } else if (($xDiff == (MarbleSolitaire::MIDDLE_INDEX - 1) && $yDiff == 0)
                || ($yDiff == (MarbleSolitaire::MIDDLE_INDEX - 1) && $xDiff == 0)) {
            $class = self::CLASS_D;
        } else if ($xDiff == 1 && $yDiff == 1) {
            $class = self::CLASS_E;
        } else if (($xDiff == 1 && $yDiff == 0) || ($yDiff == 1 && $xDiff == 0)) {
            $class = self::CLASS_F;
        } else if ($xDiff == 0 && $yDiff == 0) {
            $class = self::CLASS_G;
        }

        unset($cell);
        unset($xDiff);
        unset($yDiff);

        return $class;
    }

    /**
     * returns the total weight of all set cells
     * @param <type> $individual
     * @param <type> $board 
     */
    public function calculateTotalWeight($individual, $board) {
        $weight = 0;
        foreach ($board as $x => $row) {
            foreach ($row as $y => $cell) {
                if ($cell == MarbleSolitaire::FULL_CELL) {
                    $class = $this->determineCellClass($x, $y);
                    $weight += $individual[$class];
                }
            }
        }
        return $weight;
    }

    /**
     * sorts moves according to the total weights obtained after making the move
     *
     * @param <type> $individual
     * @param <type> $board
     * @param <type> $setCells
     * @return array all moves and their correspoding boards,
     *  sorted by the bigger obtained total weight first
     */
    public function sortMoves($individual, $board, $setCells) {
        $allowedMoves = array();
        $maxWeight = 0;
        foreach ($setCells as $cell) {
            $tempAllowedMoves = MarbleSolitaire::getPossibleMoves($board, $cell, false);
            $sortedTempMoves = array();
            foreach ($tempAllowedMoves as $move) {
                $tempBoard = MarbleSolitaire::jump($cell, $move, $board);
                $totalWeight = $this->calculateTotalWeight($individual, $tempBoard);
                $allowedMoves[$totalWeight][] = array(self::INDIVIDUAL_MOVE_INDEX => array($cell, $move), self::BOARD_INDEX => $tempBoard);
                $maxWeight = ($maxWeight < $totalWeight) ? ($totalWeight) : ($maxWeight);
            }
        }
        unset($maxWeight);
        krsort($allowedMoves); //choose the bigger weight first
        $allowedMoves = array_values($allowedMoves);
        return $allowedMoves;
    }

    /**
     * determines the fitnes of the individual
     * and the moves used to calculate that fitness
     * @param <type> $individual
     * @param <type> $board
     * @return <type>
     */
    public function getFitnessAndMoves($individual, $board) {

        $moves = array();
        $setCells = MarbleSolitaire::getAllSetCells($board);
        $fitness = count($setCells);
        $allowedMoves = $this->sortMoves($individual, $board, $setCells);
        $result = null;
        if (isset($allowedMoves[0])) {
            if (count($setCells) > self::CHANGE_OF_CRITERIA) {
                $result = $this->getFitnessAndMoves($individual, $allowedMoves[0][0][self::BOARD_INDEX]);
                if ($result != null) {
                    list($fitness, $moves) = $result;
                }
                //$moves = array_merge($moves, $allowedMoves[0][0][self::INDIVIDUAL_MOVE_INDEX]);
                $moves[] = $allowedMoves[0][0][self::INDIVIDUAL_MOVE_INDEX];
            } else {
                list($fitness, $moves) = $this->getBestPath($individual, $allowedMoves[0][0][self::BOARD_INDEX]);
                $moves[] = $allowedMoves[0][0][self::INDIVIDUAL_MOVE_INDEX];
            }
            $result = (array($fitness, $moves));
        }

        unset($moves);
        unset($setCells);
        unset($fitness);
        unset($allowedMoves);
        return $result;
    }

    /**
     * evaluates each individual,
     * for each of them returns its fitness and the moves used to calculate it
     * 
     * @param population array with all the individuals, 
     * each individual containing its set of weights
     * @return mixed array $population array(fitness => array(weights(array), moves(array)))
     */
    public function sortPopulation($population) {
        $sortedPopulation = array();
        $board = MarbleSolitaire::getStartingBoard();
        foreach ($population as $index => $individual) {
            list($fitness, $moves) = $this->getFitnessAndMoves($individual, $board);
            $sortedPopulation[$fitness][] = array(self::WEIGHTS_INDEX => $individual, self::MOVES_INDEX => $moves, self::ORIGINAL_INDEX => $index);
        }
        unset($board);
        ksort($sortedPopulation);
        return $sortedPopulation;
    }

    /**
     * Generates the initial population of weights
     * @param <type> $board
     * @return array
     */
    public function generatePopulation($size) {
        $population = array();
        for ($i = 0; $i < $size; $i++) {
            $individual = array(); //each individual is a set of 7 different weights
            for ($j = 0; $j < self::WEIGHT_COUNT; $j++) {
                $individual[$j] = (int) round(mt_rand(self::MIN_WEIGHT, self::MAX_WEIGHT) * $j / self::MAX_WEIGHT);
            }
            $population[$i] = $individual;
        }
        return $population;
    }

    /**
     * finds a solution using a genetic algorithm
     */
    public function solve() {
        $board = MarbleSolitaire::getStartingBoard();
        $population = $this->generatePopulation(self::POP_SIZE);
        $sortedPopulation = $this->sortPopulation($population);
        $solution = array();
        $i = 0;
        while ($solution == null) {
            if (!isset($sortedPopulation[self::IDEAL_FITNESS])) {
                $selection = $this->chooseIndividuals($sortedPopulation);
                $keys = array_keys($sortedPopulation);
                rsort($keys);
                $indexOfLastSection = $keys[0];                
                $indexOfLastIndividual = $sortedPopulation[$indexOfLastSection][0][self::ORIGINAL_INDEX];
                $population[$indexOfLastIndividual] = $this->combineIndividuals($selection);
                $sortedPopulation = $this->sortPopulation($population);
            } else {
                $solution = $sortedPopulation[self::IDEAL_FITNESS][0][self::MOVES_INDEX];
            }
            $i++;
            if ($i >= self::MAX_TRIES) {
                $population = $this->generatePopulation($board, $size);
                $sortedPopulation = $this->sortPopulation($population);
            }
        }
        unset($population);
        unset($sortedPopulation);
        unset($board);
        return $solution;
    }

}

?>
