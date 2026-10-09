<?php

session_start();

date_default_timezone_set("America/Toronto");

require_once "config.php";

if (!isset($_SESSION["id"])) {
    header("Location: connexion.php");
    exit;
}

$message = "";

// EMPRUNTER UN BIEN
if (isset($_POST["emprunter"])) {

    $bien_id = $_POST["bien_id"];
    $date_retour = $_POST["date_retour"];
    $usager_id = $_SESSION["id"];

    $aujourd_hui = date("Y-m-d");
    $date_max = date("Y-m-d", strtotime("+30 days"));

    if ($date_retour < $aujourd_hui) {

        $message = "La date de retour ne peut pas être dans le passé.";

    } elseif ($date_retour > $date_max) {

        $message = "Un emprunt ne peut pas dépasser 30 jours.";

    } else {

        // Vérifier si le bien est déjà emprunté
        $sql = "SELECT id
                FROM emprunts
                WHERE bien_id = ?
                AND date_retour_reelle IS NULL";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$bien_id]);

        if ($stmt->fetch()) {

            $message = "Ce bien est déjà emprunté.";

        } else {

            // Ajouter l'emprunt
            $sql = "INSERT INTO emprunts
                    (bien_id, usager_id, date_emprunt, date_retour_prevu)
                    VALUES (?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $bien_id,
                $usager_id,
                $aujourd_hui,
                $date_retour
            ]);

            $message = "Emprunt effectué avec succès.";
        }
    }
}

// RETOURNER UN BIEN

if (isset($_POST["retourner"])) {

    $emprunt_id = $_POST["id"];

    if ($_SESSION["role"] == "admin") {

        $sql = "UPDATE emprunts
                SET date_retour_reelle = CURDATE()
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$emprunt_id]);

    } else {

        $sql = "UPDATE emprunts
                SET date_retour_reelle = CURDATE()
                WHERE id = ?
                AND usager_id = ?
                AND date_retour_reelle IS NULL";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $emprunt_id,
            $_SESSION["id"]
        ]);
    }

    $message = "Le bien a été retourné.";
}

// BIENS DISPONIBLES

$sql = "SELECT b.id, b.nom, b.actif, e.id AS emprunt_id
        FROM biens b
        LEFT JOIN emprunts e
            ON b.id = e.bien_id
            AND e.date_retour_reelle IS NULL
        WHERE b.actif = 1
        
        ORDER BY b.nom";

$biens = $pdo->query($sql)->fetchAll();

// MES EMPRUNTS
$sql = "SELECT
            e.id,
            e.date_emprunt,
            e.date_retour_prevu,
            e.date_retour_reelle,
            b.nom AS bien
        FROM emprunts e
        JOIN biens b ON e.bien_id = b.id
        WHERE e.usager_id = ?
        ORDER BY e.date_emprunt DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION["id"]]);

$emprunts = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <title>Emprunts</title>

</head>

<body>

<div class="container">

    <h1>Emprunts</h1>

    <p>

        <a href="index.php">Accueil</a>

        <a href="biens.php">Biens</a>

        <a href="contributions.php">Contributions</a>

        <?php if ($_SESSION["role"] == "admin"): ?>

            <a href="admin.php">Administration</a>

        <?php endif; ?>

        <a href="deconnexion.php">Déconnexion</a>

    </p>


    <?php if ($message != ""): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <h2>Emprunter un bien</h2>

    <?php if (count($biens) == 0): ?>

        <p>Aucun bien n'est disponible actuellement.</p>

    <?php else: ?>

        <form method="POST">

            <label>Bien :</label>

            <select name="bien_id" required>

                <option value="">Choisir un bien</option>

                <?php foreach ($biens as $bien): ?>

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

    <?php endif; ?>


    <h2>Mes emprunts</h2>

    <?php if (count($emprunts) == 0): ?>

        <p>Vous n'avez aucun emprunt.</p>

    <?php else: ?>

        <table>

            <tr>

                <th>Bien</th>

                <th>Date d'emprunt</th>

                <th>Retour prévu</th>

                <th>Retour réel</th>

                <th>Action</th>

            </tr>


            <?php foreach ($emprunts as $emprunt): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars($emprunt["bien"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($emprunt["date_emprunt"]) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($emprunt["date_retour_prevu"]) ?>
                    </td>

                    <td>

                        <?php if ($emprunt["date_retour_reelle"] == null): ?>

                            <span class="en-cours">
                                En cours
                            </span>

                        <?php else: ?>

                            <span class="retourne">
                                <?= htmlspecialchars($emprunt["date_retour_reelle"]) ?>
                            </span>

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

                            Déjà retourné

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    <?php endif; ?>

</div>

</body>

</html>
