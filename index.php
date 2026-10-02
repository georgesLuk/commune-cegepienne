<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: connexion.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <title>La commune cégépienne</title>

   

</head>

<body>

<header>

    <h1>La commune cégépienne</h1>

    <nav>

        <a href="index.php">Accueil</a>
        <a href="biens.php">Biens</a>
        <a href="contributions.php">Contributions</a>
        <a href="emprunts.php">Emprunts</a>

        <?php if ($_SESSION["role"] == "admin") { ?>

            <a href="admin.php">Administration</a>

        <?php } ?>

        <a href="deconnexion.php">Déconnexion</a>

    </nav>

</header>

<main>

    <div class="boite">

        <h2>Bienvenue <?= htmlspecialchars($_SESSION["prenom"]) ?> !</h2>

        <p>
            Bienvenue sur la plateforme de la commune cégépienne.
        </p>

        <p>
            Vous pouvez partager des biens, contribuer à leur achat
            et emprunter les biens des autres membres.
        </p>

    </div>

    <div class="boite">

        <h2>Que voulez-vous faire ?</h2>

        <p>
            <a class="bouton" href="biens.php">
                Voir les biens
            </a>
        </p>

        <p>
            <a class="bouton" href="contributions.php">
                Mes contributions
            </a>
        </p>

        <p>
            <a class="bouton" href="emprunts.php">
                Mes emprunts
            </a>
        </p>

    </div>

</main>

</body>
</html>