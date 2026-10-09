<?php

session_start();

require_once "config.php";

if (!isset($_SESSION["id"])) {
    header("Location: connexion.php");
    exit;
}

// RÉCUPÉRER TOUS LES BIENS
$sql = "SELECT
            b.id,
            b.nom,
            b.description,
            b.categorie,
            b.prix_achat,
            b.date_achat,
            b.actif,
            u.prenom,
            u.nom AS nom_gardien
        FROM biens b
        LEFT JOIN usagers u ON b.gardien_id = u.id
        ORDER BY b.id DESC";

$stmt = $pdo->query($sql);

$biens = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <title>Les biens</title>

</head>

<body>

<div class="container">

    <h1>La commune cégépienne</h1>

    <div class="menu">

        <a href="index.php">Accueil</a>

        <a href="biens.php">Biens</a>

        <a href="contributions.php">Contributions</a>

        <a href="emprunts.php">Emprunts</a>
       
        <?php if ($_SESSION["role"] == "admin"): ?>

            <a href="admin.php">Administration</a>

        <?php endif; ?>

        <a href="deconnexion.php">Déconnexion</a>

    </div>

    <h2>Tous les biens</h2>

    <p>
        <a href="ajouter.php">Ajouter un bien</a>
    </p>

    <p>
        Nombre de biens : <?= count($biens) ?>
    </p>


    <?php if (count($biens) == 0): ?>

        <p>Aucun bien n'a été ajouté.</p>

    <?php else: ?>

        <?php foreach ($biens as $bien): ?>

            <?php

            // Vérifier si le bien est actuellement emprunté

            $sql_emprunt = "SELECT
                                e.id,
                                e.date_emprunt,
                                e.date_retour_prevu,
                                u.prenom,
                                u.nom
                            FROM emprunts e
                            JOIN usagers u ON e.usager_id = u.id
                            WHERE e.bien_id = ?
                            AND e.date_retour_reelle IS NULL
                            LIMIT 1";

            $stmt_emprunt = $pdo->prepare($sql_emprunt);

            $stmt_emprunt->execute([$bien["id"]]);

            $emprunt = $stmt_emprunt->fetch();

            ?>

            <div class="bien">

                <h2>
                    <?= htmlspecialchars($bien["nom"]) ?>
                </h2>


                <p>
                    <strong>Catégorie :</strong>
                    <?= htmlspecialchars($bien["categorie"]) ?>
                </p>


                <p>
                    <strong>Description :</strong>
                    <?= htmlspecialchars($bien["description"]) ?>
                </p>

                <p>
                    <strong>Prix :</strong>
                    <?= number_format($bien["prix_achat"], 2) ?> $
                </p>

                <p>
                    <strong>Date d'achat :</strong>
                    <?= htmlspecialchars($bien["date_achat"]) ?>
                </p>

                <p>
                    <strong>Gardien :</strong>

                    <?php if ($bien["prenom"] != null): ?>

                        <?= htmlspecialchars($bien["prenom"]) ?>
                        <?= htmlspecialchars($bien["nom_gardien"]) ?>

                    <?php else: ?>

                        Aucun gardien

                    <?php endif; ?>

                </p>

                <?php if ($bien["actif"] == 0): ?>

                    <p class="inactif">
                        Bien inactif
                    </p>

                <?php elseif ($emprunt): ?>

                    <p class="emprunte">

                        Emprunté par :
                        <?= htmlspecialchars($emprunt["prenom"]) ?>
                        <?= htmlspecialchars($emprunt["nom"]) ?>

                        <br>

                        Retour prévu :
                        <?= htmlspecialchars($emprunt["date_retour_prevu"]) ?>

                    </p>

                <?php else: ?>

                    <p class="disponible">
                        Disponible
                    </p>

                <?php endif; ?>

                <a
                    class="bouton"
                    href="modifier.php?id=<?= $bien["id"] ?>"
                >
                    Modifier
                </a>

                <a
                    class="bouton"
                    href="supprimer.php?id=<?= $bien["id"] ?>"
                >
                    Supprimer
                </a>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

</body>

</html>