<?php

session_start();
require_once "config.php";

if (!isset($_SESSION["id"])) {
    header("Location: connexion.php");
    exit;
}

$id = $_GET["id"];

$sql = "SELECT * FROM biens WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

$bien = $stmt->fetch();

if (!$bien) {
    die("Bien introuvable.");
}

if ($bien["gardien_id"] != $_SESSION["id"] && $_SESSION["role"] != "admin") {
    die("Vous n'avez pas le droit de retirer ce bien.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $sql = "SELECT id
            FROM emprunts
            WHERE bien_id = ?
            AND date_retour_reelle IS NULL";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    $emprunt = $stmt->fetch();

    if ($emprunt) {

        die("Impossible de retirer un bien actuellement emprunté.");

    } else {

        $sql = "UPDATE biens
                SET actif = 0
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        header("Location: biens.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <title>Retirer un bien</title>

    

</head>

<body>

<div class="boite">

    <h1>Retirer le bien</h1>

    <p>
        Voulez-vous retirer
        <strong><?= htmlspecialchars($bien["nom"]) ?></strong> ?
    </p>

    <form method="POST">

        <button type="submit">
            Oui, retirer
        </button>

    </form>

    <br>

    <a href="biens.php">Annuler</a>

</div>

</body>
</html>