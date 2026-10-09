<?php

session_start();

require_once "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $prenom = $_POST["prenom"];
    $nom = $_POST["nom"];
    $courriel = $_POST["courriel"];
    $mot_de_passe = $_POST["mot_de_passe"];
    $confirmation = $_POST["confirmation"];

    if ($mot_de_passe != $confirmation) {

        $message = "Les mots de passe ne sont pas identiques.";

    } elseif (strlen($mot_de_passe) < 8) {

        $message = "Le mot de passe doit contenir au moins 8 caractères.";

    } else {

        $sql = "SELECT id FROM usagers WHERE courriel = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$courriel]);

        if ($stmt->fetch()) {

            $message = "Ce courriel existe déjà.";

        } else {

            $mot_de_passe_hash = password_hash(
                $mot_de_passe,
                PASSWORD_DEFAULT
            );

            $sql = "INSERT INTO usagers
                    (prenom, nom, courriel, mot_de_passe, role, karma, date_creation)
                    VALUES (?, ?, ?, ?, 'membre', 0, NOW())";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $prenom,
                $nom,
                $courriel,
                $mot_de_passe_hash
            ]);

            header("Location: connexion.php");
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <title>Inscription</title>


</head>

<body>

<div class="formulaire">

    <h1>Créer un compte</h1>

    <?php if ($message != "") { ?>

        <p class="erreur"><?= htmlspecialchars($message) ?></p>

    <?php } ?>

    <form method="POST">

        <label>Prénom :</label>
        <input type="text" name="prenom" required>

        <label>Nom :</label>
        <input type="text" name="nom" required>

        <label>Courriel :</label>
        <input type="email" name="courriel" required>

        <label>Mot de passe :</label>
        <input type="password" name="mot_de_passe" required>

        <label>Confirmation :</label>
        <input type="password" name="confirmation" required>

        <button type="submit">S'inscrire</button>

    </form>

    <p>
        <a href="connexion.php">Retour à la connexion</a>
    </p>

</div>

</body>
</html>