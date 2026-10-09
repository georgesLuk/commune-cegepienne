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
    die("Vous n'avez pas le droit de modifier ce bien.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nom = $_POST["nom"];
    $description = $_POST["description"];
    $categorie = $_POST["categorie"];
    $prix = $_POST["prix"];

    $sql = "SELECT SUM(montant)
            FROM contributions
            WHERE bien_id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    $total = $stmt->fetchColumn();

    if ($total == null) {
        $total = 0;
    }

    if ($prix < $total) {

        $message = "Le prix ne peut pas être inférieur aux contributions.";

    } else {

        $sql = "UPDATE biens
                SET nom = ?,
                    description = ?,
                    categorie = ?,
                    prix_achat = ?
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $nom,
            $description,
            $categorie,
            $prix,
            $id
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
    <title>Modifier un bien</title>

    

    </style>

</head>

<body>

<div class="formulaire">

    <h1>Modifier le bien</h1>

    <?php if ($message != "") { ?>

        <p class="erreur"><?= htmlspecialchars($message) ?></p>

    <?php } ?>

    <form method="POST">

        <label>Nom :</label>

        <input type="text"
               name="nom"
               value="<?= htmlspecialchars($bien["nom"]) ?>"
               required>

        <label>Description :</label>

        <textarea name="description"><?= htmlspecialchars($bien["description"]) ?></textarea>

        <label>Catégorie :</label>

        <input type="text"
               name="categorie"
               value="<?= htmlspecialchars($bien["categorie"]) ?>"
               required>

        <label>Prix :</label>

        <input type="number"
               name="prix"
               value="<?= $bien["prix_achat"] ?>"
               step="0.01"
               min="0"
               required>

        <button type="submit">Modifier</button>

    </form>

    <br>

    <a href="biens.php">Retour</a>

</div>

</body>
</html>