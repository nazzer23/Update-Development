<?php
class Database {
    public $db;

    public function __construct() {
        $this->db = new mysqli(Configuration::dbHost, Configuration::dbUser, Configuration::dbPass, Configuration::dbName);
        if($this->db->connect_errno) {
            die("There was an error connecting to the database. " . $this->db->connect_error);
        }
    }

    public function executeQuery($query) {
        return $this->db->query($query);
    }

    public function fetchArray($query) {    
        return $this->executeQuery($query)->fetch_array();
    }

    public function fetchObject($query) {    
        return $this->executeQuery($query)->fetch_object();
    }

    public function getNumberOfRows($query) {
        return $this->executeQuery($query)->num_rows;
    }

    public function escapeArray($arr) {
        $tempArr = $arr;
        foreach($tempArr as $key => $value) {
            $tempArr[$key] = $this->escapeString($value);
        }
        return $tempArr;
    }

    public function escapeString($string) {
        return $this->db->real_escape_string($string);
    }
}
?>