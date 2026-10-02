<?php

session_start();
require_once "config.php";

if (!isset($_SESSION["id"])) {
    header("Location: connexion.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nom = $_POST["nom"];
    $description = $_POST["description"];
    $categorie = $_POST["categorie"];
    $prix = $_POST["prix"];

    if ($prix < 0) {

        $message = "Le prix ne peut pas être négatif.";

    } else {

        $sql = "INSERT INTO biens
                (nom, description, categorie, prix_achat,
                 date_achat, gardien_id, actif)
                VALUES (?, ?, ?, ?, CURDATE(), ?, 1)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $nom,
            $description,
            $categorie,
            $prix,
            $_SESSION["id"]
        ]);

        header("Location: biens.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <title>Ajouter un bien</title>

</head>

<body>

<div class="formulaire">

    <h1>Ajouter un bien</h1>

    <?php if ($message != "") { ?>

        <p class="erreur"><?= htmlspecialchars($message) ?></p>

    <?php } ?>

    <form method="POST">

        <label>Nom :</label>
        <input type="text" name="nom" required>

        <label>Description :</label>
        <textarea name="description"></textarea>

        <label>Catégorie :</label>
        <input type="text" name="categorie" required>

        <label>Prix d'achat :</label>
        <input type="number" name="prix" step="0.01" min="0" required>

        <button type="submit">Ajouter</button>

    </form>

    <br>

    <a href="biens.php">Retour aux biens</a>

</div>

</body>
</html>