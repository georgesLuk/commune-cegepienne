<?php

$serveur = "localhost";
$baseDeDonnees = "commune"
$utilisateur = "root";
$motDePasse = "";

try{
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $utilisateur, $motDePasse);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo ("Connexion réussie!!!");
   
}
catch (PDOException $e) {

    die("Erreur de connexion : " . $e->getMessage());

}

?>