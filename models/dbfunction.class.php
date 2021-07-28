<?php
class DBFUNCTIONS{
    function __construct(){
        include "../config/config.php";

        $this->mysqli = new mysqli('127.0.0.1', $user, $password, $database, $port);

        if ($this->mysqli->connect_error) {
            die('Connect Error (' . $this->mysqli->connect_errno . ') '
                    . $this->mysqli->connect_error);
        }
        
    }

    // Function to execute query passed in it
    function execQuery($qry){
        $result = mysqli_query($this->mysqli, $qry);
        return $result;
    }

    function execQueryAndGetId($qry){
        mysqli_query($this->mysqli, $qry);
        return mysqli_insert_id($this->mysqli);
    }
}

?>