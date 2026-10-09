<?php

session_start();

require_once "config.php";

if (!isset($_SESSION["id"])) {
    header("Location: connexion.php");
    exit;
}

if ($_SESSION["role"] != "admin") {
    die("Accès refusé.");
}

$message = "";

// AJOUTER UN USAGER
if (isset($_POST["ajouter"])) {

    $prenom = $_POST["prenom"];
    $nom = $_POST["nom"];
    $courriel = $_POST["courriel"];
    $mot_de_passe = $_POST["mot_de_passe"];
    $role = $_POST["role"];

    if (strlen($mot_de_passe) < 8) {

        $message = "Le mot de passe doit avoir au moins 8 caractères.";

    } else {

        // Vérifier si le courriel existe déjà
        $sql = "SELECT id
                FROM usagers
                WHERE courriel = ?";

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
                    VALUES (?, ?, ?, ?, ?, 0, NOW())";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $prenom,
                $nom,
                $courriel,
                $mot_de_passe_hash,
                $role
            ]);

            $message = "Usager ajouté avec succès.";
        }
    }
}

// CHANGER LE RÔLE
if (isset($_POST["changer_role"])) {

    $id = $_POST["id"];
    $nouveau_role = $_POST["role"];

    // Vérifier le rôle actuel
    $sql = "SELECT role
            FROM usagers
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    $usager = $stmt->fetch();

    if ($usager) {

        // Empêcher de retirer le dernier admin
        if (
            $usager["role"] == "admin"
            && $nouveau_role == "membre"
        ) {

            $sql = "SELECT COUNT(*) AS total
                    FROM usagers
                    WHERE role = 'admin'";

            $total_admins = $pdo->query($sql)->fetch()["total"];

            if ($total_admins <= 1) {

                $message = "Impossible de rétrograder le dernier administrateur.";

            } else {

                $sql = "UPDATE usagers
                        SET role = ?
                        WHERE id = ?";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nouveau_role, $id]);

                $message = "Rôle modifié.";
            }

        } else {

            $sql = "UPDATE usagers
                    SET role = ?
                    WHERE id = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nouveau_role, $id]);

            $message = "Rôle modifié.";
        }
    }
}

// RÉINITIALISER LE MOT DE PASSE
if (isset($_POST["reset_password"])) {

    $id = $_POST["id"];

    $nouveau_mot_de_passe = password_hash(
        "password123",
        PASSWORD_DEFAULT
    );

    $sql = "UPDATE usagers
            SET mot_de_passe = ?
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $nouveau_mot_de_passe,
        $id
    ]);

    $message = "Mot de passe réinitialisé à : password123";
}

// SUPPRIMER UN USAGER
if (isset($_POST["supprimer"])) {

    $id = $_POST["id"];

    // Vérifier si c'est un admin
    $sql = "SELECT role
            FROM usagers
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    $usager = $stmt->fetch();

    if ($usager) {

        // Empêcher de supprimer le dernier admin
        if ($usager["role"] == "admin") {

            $sql = "SELECT COUNT(*) AS total
                    FROM usagers
                    WHERE role = 'admin'";

            $total_admins = $pdo->query($sql)->fetch()["total"];

            if ($total_admins <= 1) {

                $message = "Impossible de supprimer le dernier administrateur.";

            } else {

                $sql = "SELECT COUNT(*) AS total
                        FROM emprunts
                        WHERE usager_id = ?
                        AND date_retour_reelle IS NULL";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);

                $emprunt = $stmt->fetch()["total"];

                if ($emprunt > 0) {

                    $message = "Impossible de supprimer cet usager car il a un emprunt en cours.";

                } else {

                    $sql = "DELETE FROM usagers
                            WHERE id = ?";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$id]);

                    $message = "Usager supprimé.";
                }
            }

        } else {

            $sql = "SELECT COUNT(*) AS total
                    FROM emprunts
                    WHERE usager_id = ?
                    AND date_retour_reelle IS NULL";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);

            $emprunt = $stmt->fetch()["total"];

            if ($emprunt > 0) {

                $message = "Impossible de supprimer cet usager car il a un emprunt en cours.";

            } else {

                $sql = "DELETE FROM usagers
                        WHERE id = ?";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);

                $message = "Usager supprimé.";
            }
        }
    }
}

// LISTE DES USAGERS
$sql = "SELECT *
        FROM usagers
        ORDER BY id";

$usagers = $pdo->query($sql)->fetchAll();

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <title>Gestion des usagers</title>

    
</head>

<body>

<div class="container">

    <h1>Gestion des usagers</h1>

    <p>

        <a href="index.php">Accueil</a>

        <a href="biens.php">Biens</a>

        <a href="contributions.php">Contributions</a>

        <a href="emprunts.php">Emprunts</a>

        <a href="deconnexion.php">Déconnexion</a>

    </p>

    <?php if ($message != ""): ?>

        <div class="message">

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>

    <h2>Ajouter un usager</h2>

    <form method="POST">

        <input
            type="text"
            name="prenom"
            placeholder="Prénom"
            required
        >

        <input
            type="text"
            name="nom"
            placeholder="Nom"
            required
        >

        <input
            type="email"
            name="courriel"
            placeholder="Courriel"
            required
        >

        <input
            type="password"
            name="mot_de_passe"
            placeholder="Mot de passe"
            required
        >

        <select name="role">

            <option value="membre">
                Membre
            </option>

            <option value="admin">
                Administrateur
            </option>

        </select>

        <button type="submit" name="ajouter">
            Ajouter
        </button>

    </form>

    <h2>Liste des usagers</h2>

    <table>

        <tr>
            <th>ID</th>

            <th>Prénom</th>

            <th>Nom</th>

            <th>Courriel</th>

            <th>Rôle</th>

            <th>Karma</th>

            <th>Actions</th>

        </tr>

        <?php foreach ($usagers as $usager): ?>

            <tr>

                <td>
                    <?= $usager["id"] ?>
                </td>

                <td>
                    <?= htmlspecialchars($usager["prenom"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($usager["nom"]) ?>
                </td>

                <td>
                    <?= htmlspecialchars($usager["courriel"]) ?>
                </td>

                <td>
                    <form method="POST">

                        <input
                            type="hidden"
                            name="id"
                            value="<?= $usager["id"] ?>"
                        >
                        <select name="role">

                            <option
                                value="membre"
                                <?= $usager["role"] == "membre" ? "selected" : "" ?>
                            >
                                Membre
                            </option>

                            <option
                                value="admin"
                                <?= $usager["role"] == "admin" ? "selected" : "" ?>
                            >
                                Admin
                            </option>

                        </select>

                        <button
                            type="submit"
                            name="changer_role"
                        >
                            Modifier
                        </button>

                    </form>

                </td>
                <td>
                    <?= $usager["karma"] ?>
                </td>

                <td>
                    <form method="POST">

                        <input
                            type="hidden"
                            name="id"
                            value="<?= $usager["id"] ?>"
                        >
                        <button
                            type="submit"
                            name="reset_password"
                        >
                            Réinitialiser mot de passe
                        </button>

                    </form>

                    <form method="POST">
                        <input
                            type="hidden"
                            name="id"
                            value="<?= $usager["id"] ?>"
                        >
                        <button
                            type="submit"
                            name="supprimer"
                        >
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