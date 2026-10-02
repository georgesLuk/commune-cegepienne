<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["id"])) {
    header("Location: connexion.php");
    exit;
}

$message = "";

/* Ajouter une contribution */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["ajouter"])) {

    $bien_id = $_POST["bien_id"];
    $montant = $_POST["montant"];
    $usager_id = $_SESSION["id"];

    if ($montant <= 0) {
        $message = "Le montant doit être supérieur à 0.";
    } else {

        /* Prix du bien */
        $sql = "SELECT prix_achat FROM biens WHERE id = ? AND actif = 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$bien_id]);
        $bien = $stmt->fetch();

        if (!$bien) {
            $message = "Bien introuvable.";
        } else {

            /* Total déjà financé */
            $sql = "SELECT COALESCE(SUM(montant), 0) AS total
                    FROM contributions
                    WHERE bien_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$bien_id]);
            $total = $stmt->fetch()["total"];

            if (($total + $montant) > $bien["prix_achat"]) {
                $message = "Le total des contributions ne peut pas dépasser le prix du bien.";
            } else {

                $sql = "INSERT INTO contributions
                        (usager_id, bien_id, montant, date_contribution)
                        VALUES (?, ?, ?, CURDATE())";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([$usager_id, $bien_id, $montant]);

                $message = "Contribution ajoutée avec succès.";
            }
        }
    }
}


/* Supprimer une contribution */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["supprimer"])) {

    $id = $_POST["id"];

    if ($_SESSION["role"] == "admin") {

        $sql = "DELETE FROM contributions WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

    } else {

        $sql = "DELETE FROM contributions
                WHERE id = ? AND usager_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $_SESSION["id"]]);
    }

    $message = "Contribution supprimée.";
}


/* Liste des biens */
$sql = "SELECT id, nom, prix_achat
        FROM biens
        WHERE actif = 1
        ORDER BY nom";
$biens = $pdo->query($sql)->fetchAll();


/* Liste des contributions */
if ($_SESSION["role"] == "admin") {

    $sql = "SELECT contributions.*,
                   biens.nom AS bien_nom,
                   usagers.prenom,
                   usagers.nom
            FROM contributions
            JOIN biens ON contributions.bien_id = biens.id
            JOIN usagers ON contributions.usager_id = usagers.id
            ORDER BY date_contribution DESC";

    $contributions = $pdo->query($sql)->fetchAll();

} else {

    $sql = "SELECT contributions.*,
                   biens.nom AS bien_nom
            FROM contributions
            JOIN biens ON contributions.bien_id = biens.id
            WHERE contributions.usager_id = ?
            ORDER BY date_contribution DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION["id"]]);
    $contributions = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Contributions</title>

</head>

<body>

<div class="conteneur">

    <h1>Contributions</h1>

    <p>
        <a href="index.php">Accueil</a>
        <a href="biens.php">Biens</a>
        <a href="emprunts.php">Emprunts</a>
    </p>

    <?php if ($message != ""): ?>
        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>


    <h2>Ajouter une contribution</h2>

    <form method="POST">

        <label>Bien :</label>

        <select name="bien_id" required>

            <option value="">Choisir un bien</option>

            <?php foreach ($biens as $bien): ?>

                <option value="<?= $bien["id"] ?>">
                    <?= htmlspecialchars($bien["nom"]) ?>
                    - <?= number_format($bien["prix_achat"], 2) ?> $
                </option>

            <?php endforeach; ?>

        </select>

        <label>Montant :</label>

        <input
            type="number"
            name="montant"
            step="0.01"
            min="0.01"
            required
        >

        <button type="submit" name="ajouter">
            Ajouter
        </button>

    </form>


    <h2>
        <?php
        if ($_SESSION["role"] == "admin") {
            echo "Toutes les contributions";
        } else {
            echo "Mes contributions";
        }
        ?>
    </h2>

    <table>

        <tr>
            <th>Bien</th>

            <?php if ($_SESSION["role"] == "admin"): ?>
                <th>Utilisateur</th>
            <?php endif; ?>

            <th>Montant</th>
            <th>Date</th>
            <th>Action</th>
        </tr>


        <?php foreach ($contributions as $contribution): ?>

            <tr>

                <td>
                    <?= htmlspecialchars($contribution["bien_nom"]) ?>
                </td>

                <?php if ($_SESSION["role"] == "admin"): ?>

                    <td>
                        <?= htmlspecialchars($contribution["prenom"]) ?>
                        <?= htmlspecialchars($contribution["nom"]) ?>
                    </td>

                <?php endif; ?>

                <td>
                    <?= number_format($contribution["montant"], 2) ?> $
                </td>

                <td>
                    <?= htmlspecialchars($contribution["date_contribution"]) ?>
                </td>

                <td>

                    <form method="POST">

                        <input
                            type="hidden"
                            name="id"
                            value="<?= $contribution["id"] ?>"
                        >

                        <button type="submit" name="supprimer">
                            Supprimer
                        </button>

                    </form>

                </td>

            </tr>

        <?php endforeach; ?>

    </table>

</div>

</body>
</html>