<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["id"])) {
    header("Location: connexion.php");
    exit;
}

// L'ID du compte connecté
$id = $_SESSION["id"];
$message = "";

// Modifier l'usager
if (isset($_POST["modifier"])) {
    $prenom = trim($_POST["prenom"]);
    $nom = trim($_POST["nom"]);
    $courriel = trim($_POST["courriel"]);

    try {
        $sql = "UPDATE usagers
                SET prenom = ?, nom = ?, courriel = ?
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$prenom, $nom, $courriel, $id]);

        $message = "Informations modifiées avec succès.";
    } catch (PDOException $e) {
        $message = "Ce courriel est déjà utilisé.";
    }
}

// Charger les informations du membre
$stmt = $pdo->prepare(
    "SELECT id, prenom, nom, courriel
     FROM usagers WHERE id = ?"
);
$stmt->execute([$id]);
$usager = $stmt->fetch();

if (!$usager) {
    die("Usager introuvable.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier mon profil</title>

</head>
<body>
<main>
<h1>Modifier mon profil</h1>

<p><?= htmlspecialchars($message) ?></p>

<form method="post">
    <label>ID de l'usager</label>
    <input value="<?= $usager["id"] ?>" disabled>

    <label>Prénom</label>
    <input name="prenom"
           value="<?= htmlspecialchars($usager["prenom"]) ?>"
           required>

    <label>Nom</label>
    <input name="nom"
           value="<?= htmlspecialchars($usager["nom"]) ?>"
           required>

    <label>Courriel</label>
    <input type="email" name="courriel"
           value="<?= htmlspecialchars($usager["courriel"]) ?>"
           required>

    <button type="submit" name="modifier">Enregistrer</button>
</form>

<a href="index.php">Accueil</a>
</main>
</body>
</html>