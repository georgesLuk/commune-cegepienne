<?php

session_start();

require_once "config.php";

if (!isset($_SESSION["id"])) {
    header("Location: connexion.php");
    exit;
}

$sql = "SELECT biens.*, 
               usagers.prenom,
               usagers.nom
        FROM biens
        JOIN usagers ON biens.gardien_id = usagers.id
        WHERE biens.actif = 1
        ORDER BY biens.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$biens = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <title>Les biens</title>

   

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

    <div class="haut">

        <h2>Les biens disponibles</h2>

        <a class="bouton" href="ajouter.php">
            Ajouter un bien
        </a>

    </div>

    <br>

    <div class="biens">

        <?php foreach ($biens as $bien) { ?>

            <div class="carte">

                <h2><?= htmlspecialchars($bien["nom"]) ?></h2>

                <p>
                    <strong>Catégorie :</strong>
                    <?= htmlspecialchars($bien["categorie"]) ?>
                </p>

                <p>
                    <?= htmlspecialchars($bien["description"]) ?>
                </p>

                <p>
                    <strong>Prix :</strong>
                    <?= $bien["prix_achat"] ?> $
                </p>

                <p>
                    <strong>Gardien :</strong>
                    <?= htmlspecialchars($bien["prenom"]) ?>
                    <?= htmlspecialchars($bien["nom"]) ?>
                </p>

                <?php

                $sqlEmprunt = "SELECT usagers.prenom,
                                      usagers.nom,
                                      emprunts.date_retour_prevue
                               FROM emprunts
                               JOIN usagers
                               ON emprunts.usager_id = usagers.id
                               WHERE emprunts.bien_id = ?
                               AND emprunts.date_retour_reelle IS NULL";

                $stmtEmprunt = $pdo->prepare($sqlEmprunt);
                $stmtEmprunt->execute([$bien["id"]]);

                $emprunt = $stmtEmprunt->fetch();

                ?>

                <?php if ($emprunt) { ?>

                    <p class="emprunte">
                        Emprunté par
                        <?= htmlspecialchars($emprunt["prenom"]) ?>
                        <?= htmlspecialchars($emprunt["nom"]) ?>
                    </p>

                    <p>
                        Retour prévu :
                        <?= $emprunt["date_retour_prevue"] ?>
                    </p>

                <?php } else { ?>

                    <p class="disponible">
                        Disponible
                    </p>

                <?php } ?>

                <a class="bouton"
                   href="biens.php?id=<?= $bien["id"] ?>">
                    Voir le bien
                </a>

            </div>

        <?php } ?>

    </div>

</main>

</body>
</html>