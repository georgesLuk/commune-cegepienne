<?php

session_start();

require_once "config.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $courriel = $_POST["courriel"];
    $mot_de_passe = $_POST["mot_de_passe"];

    $sql = "SELECT * FROM usagers WHERE courriel = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$courriel]);

    $usager = $stmt->fetch();

    if ($usager && password_verify($mot_de_passe, $usager["mot_de_passe"])) {

        session_regenerate_id(true);

        $_SESSION["id"] = $usager["id"];
        $_SESSION["prenom"] = $usager["prenom"];
        $_SESSION["nom"] = $usager["nom"];
        $_SESSION["role"] = $usager["role"];

        header("Location: index.php");
        exit;

    } else {

        $message = "Courriel ou mot de passe incorrect.";

    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <title>Connexion</title>

    <style>

        body {
            font-family: Arial;
            background-color: #f2f2f2;
        }

        .formulaire {
            width: 400px;
            margin: 80px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        button {
            background-color: #333;
            color: white;
            border: 0;
            padding: 10px 20px;
            cursor: pointer;
        }

        .erreur {
            color: red;
        }

    </style>

</head>

<body>

<div class="formulaire">

    <h1>Connexion</h1>

    <?php if ($message != "") { ?>

        <p class="erreur"><?= $message ?></p>

    <?php } ?>

    <form method="POST">

        <label>Courriel :</label>
        <input type="email" name="courriel" required>

        <label>Mot de passe :</label>
        <input type="password" name="mot_de_passe" required>

        <button type="submit">Se connecter</button>

    </form>

    <p>
        Pas encore de compte ?
        <a href="inscription.php">Créer un compte</a>
    </p>

</div>

</body>
</html>