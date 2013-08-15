<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

include_once 'MarbleSolitaire.php';

/**
 * Description of SolutionPersistenceManager
 *
 * @author Naty
 */
class SolutionPersistenceManager {

    private static $instance;
    private $connection;
    private $tableName = 'solution_steps';
    private $dbName = 'peg_solitaire_solutions';
    private $server = 'localhost';
    private $port = '3306';
    private $user = 'root';
    private $pass = null;

    private function __construct() {

    }

    private function connect() {
        $this->connection = mysql_connect($this->server . ':' . $this->port, $this->user, $this->pass);
        mysql_select_db($this->dbName);
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new SolutionPersistenceManager();
        }
        return self::$instance;
    }

    /**
     * gets next id to use to insert a solution
     * @return <type>
     */
    private function getNextId() {
        $this->connect();
        $sql = "select max(solution_id) as last_sol_id from {$this->tableName}";
        $result = mysql_query($sql);
        $row = null;
        if ($result) {
            $row = mysql_fetch_array($result);
        }
        mysql_close();

        unset($result);
        return ($row['last_sol_id'] + 1);
    }

    /**
     * inserts a found solution into the DB
     * @param <type> $solution
     * @return <type> true on success, false on error
     */
    public function insertSolution($solution) {
        $nextId = $this->getNextId();
        $sql = "INSERT INTO {$this->tableName} VALUES ";
        $i = 1;
        foreach ($solution as $step) {
            $sql .= "($nextId, $i, {$step[0][MarbleSolitaire::X_AXIS]},
                {$step[0][MarbleSolitaire::Y_AXIS]},
                {$step[1][MarbleSolitaire::X_AXIS]},
                {$step[1][MarbleSolitaire::Y_AXIS]}), ";
            $i++;
        }
        $sql = substr($sql, 0, strlen($sql) - 2);

        $this->connect();
        $result = mysql_query($sql);
        mysql_close();

        return $result;
    }

    /**
     * gets the solution identified by the given ID on the DB
     * @param <type> $solutionId
     * @return <type> the requested solution
     */
    public function getSolution($solutionId) {
        $sql = "SELECT * FROM {$this->tableName} WHERE solution_id = $solutionId ORDER BY solution_step";
        $solution = array();
        $this->connect();
        $result = mysql_query($sql);

        if ($result) {
            while ($row = mysql_fetch_array($result)) {
                $fromCell = array(MarbleSolitaire::X_AXIS => $row['from_x'],
                    MarbleSolitaire::Y_AXIS => $row['from_y']);
                $toCell = array(MarbleSolitaire::X_AXIS => $row['to_x'],
                    MarbleSolitaire::Y_AXIS => $row['to_y']);
                $solution[] = array($fromCell, $toCell);
            }
        }
        mysql_close();
        unset($result);

        return $solution;
    }

}

?>
