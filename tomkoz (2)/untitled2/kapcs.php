<?php
function dbkapcs(){
    $host="localhost";
    $user="root";
    $pass="";
    $db="tomkoz";
    $kapcs=mysqli_connect($host,$user,$pass,$db) or die("Hiba a kapcsolódáskor!");
    $kapcs -> set_charset("utf8");
    return $kapcs;
}

