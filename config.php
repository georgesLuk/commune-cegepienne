<?php

$host = "localhost";
$username = "root";
$password = "root";
$dbname = "commune";

try {

   

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo ("Connexion réussie!!!");

}

catch (PDOException $e) {

    die("Erreur de connexion : " . $e->getMessage());

}

