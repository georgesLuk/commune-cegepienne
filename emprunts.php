<?php
session_start();
date_default_timezone_set("America/Toronto");
require_once "config.php";

if (!isset($_SESSION["id"])) {
    header("Location: connexion.php");
    exit;
}

$message = "";


/* Emprunter un bien */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["emprunter"])) {

    $bien_id = $_POST["bien_id"];
    $date_retour = $_POST["date_retour"];
    $usager_id = $_SESSION["id"];

    $aujourd_hui = date("Y-m-d");
    $date_max = date("Y-m-d", strtotime("+30 days"));

    /* Vérifier la date */
    if ($date_retour < $aujourd_hui) {

        $message = "La date de retour ne peut pas être dans le passé.";

    } elseif ($date_retour > $date_max) {

        $message = "La durée maximale d'un emprunt est de 30 jours.";

    } else {

        /* Vérifier si le bien est déjà emprunté */
        $sql = "SELECT id
                FROM emprunts
                WHERE bien_id = ?
                AND date_retour_reelle IS NULL";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$bien_id]);

        $emprunt_existant = $stmt->fetch();

        if ($emprunt_existant) {

            $message = "Ce bien est déjà emprunté.";

        } else {

            /* Vérifier que le bien existe */
            $sql = "SELECT id FROM biens
                    WHERE id = ? AND actif = 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$bien_id]);

            $bien = $stmt->fetch();

            if (!$bien) {

                $message = "Bien introuvable.";

            } else {

                $sql = "INSERT INTO emprunts
                        (bien_id, usager_id, date_emprunt, date_retour_prevue)
                        VALUES (?, ?, CURDATE(), ?)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $bien_id,
                    $usager_id,
                    $date_retour
                ]);

                $message = "Emprunt effectué avec succès.";
            }
        }
    }
}


/* Retourner un bien */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["retourner"])) {

    $id = $_POST["id"];

    if ($_SESSION["role"] == "admin") {

        $sql = "UPDATE emprunts
                SET date_retour_reelle = CURDATE()
                WHERE id = ?
                AND date_retour_reelle IS NULL";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

    } else {

        $sql = "UPDATE emprunts
                SET date_retour_reelle = CURDATE()
                WHERE id = ?
                AND usager_id = ?
                AND date_retour_reelle IS NULL";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id,
            $_SESSION["id"]
        ]);
    }

    $message = "Retour effectué.";
}


/* Liste des biens disponibles */
$sql = "SELECT biens.id, biens.nom
        FROM biens
        WHERE biens.actif = 1
        AND biens.id NOT IN (
            SELECT bien_id
            FROM emprunts
            WHERE date_retour_reelle IS NULL
        )
        ORDER BY biens.nom";

$biens_disponibles = $pdo->query($sql)->fetchAll();


/* Liste des emprunts */
if ($_SESSION["role"] == "admin") {

    $sql = "SELECT emprunts.*,
                   biens.nom AS bien_nom,
                   usagers.prenom,
                   usagers.nom
            FROM emprunts
            JOIN biens ON emprunts.bien_id = biens.id
            JOIN usagers ON emprunts.usager_id = usagers.id
            ORDER BY emprunts.date_emprunt DESC";

    $emprunts = $pdo->query($sql)->fetchAll();

} else {

    $sql = "SELECT emprunts.*,
                   biens.nom AS bien_nom
            FROM emprunts
            JOIN biens ON emprunts.bien_id = biens.id
            WHERE emprunts.usager_id = ?
            ORDER BY emprunts.date_emprunt DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION["id"]]);

    $emprunts = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <title>Emprunts</title>

   

</head>

<body>

<div class="conteneur">

    <h1>Emprunts</h1>

    <p>

        <a href="index.php">Accueil</a>

        <a href="biens.php">Biens</a>

        <a href="contributions.php">Contributions</a>

    </p>


    <?php if ($message != ""): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <h2>Emprunter un bien</h2>

    <form method="POST">

        <label>Bien :</label>

        <select name="bien_id" required>

            <option value="">Choisir un bien</option>

            <?php foreach ($biens_disponibles as $bien): ?>

                <option value="<?= $bien["id"] ?>">

                    <?= htmlspecialchars($bien["nom"]) ?>

                </option>

            <?php endforeach; ?>

        </select>


        <label>Date de retour prévue :</label>

        <input
            type="date"
            name="date_retour"
            min="<?= date("Y-m-d") ?>"
            max="<?= date("Y-m-d", strtotime("+30 days")) ?>"
            required
        >


        <button type="submit" name="emprunter">

            Emprunter

        </button>

    </form>


    <h2>

        <?php

        if ($_SESSION["role"] == "admin") {

            echo "Tous les emprunts";

        } else {

            echo "Mes emprunts";

        }

        ?>

    </h2>


    <table>

        <tr>

            <th>Bien</th>

            <?php if ($_SESSION["role"] == "admin"): ?>

                <th>Utilisateur</th>

            <?php endif; ?>

            <th>Date d'emprunt</th>

            <th>Retour prévu</th>

            <th>Retour réel</th>

            <th>État</th>

            <th>Action</th>

        </tr>


        <?php foreach ($emprunts as $emprunt): ?>

            <tr>

                <td>

                    <?= htmlspecialchars($emprunt["bien_nom"]) ?>

                </td>


                <?php if ($_SESSION["role"] == "admin"): ?>

                    <td>

                        <?= htmlspecialchars($emprunt["prenom"]) ?>

                        <?= htmlspecialchars($emprunt["nom"]) ?>

                    </td>

                <?php endif; ?>


                <td>

                    <?= htmlspecialchars($emprunt["date_emprunt"]) ?>

                </td>


                <td>

                    <?= htmlspecialchars($emprunt["date_retour_prevue"]) ?>

                </td>


                <td>

                    <?php

                    if ($emprunt["date_retour_reelle"] == null) {

                        echo "-";

                    } else {

                        echo htmlspecialchars(
                            $emprunt["date_retour_reelle"]
                        );

                    }

                    ?>

                </td>


                <td>

                    <?php if ($emprunt["date_retour_reelle"] == null): ?>

                        <span class="en-cours">
                            En cours
                        </span>

                    <?php else: ?>

                        Terminé

                    <?php endif; ?>

                </td>


                <td>

                    <?php if ($emprunt["date_retour_reelle"] == null): ?>

                        <form method="POST">

                            <input
                                type="hidden"
                                name="id"
                                value="<?= $emprunt["id"] ?>"
                            >

                            <button type="submit" name="retourner">

                                Retourner

                            </button>

                        </form>

                    <?php else: ?>

                        -

                    <?php endif; ?>

                </td>

            </tr>

        <?php endforeach; ?>

    </table>

</div>

</body>

</html>