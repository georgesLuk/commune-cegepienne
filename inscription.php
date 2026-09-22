<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
</head>
<body>
    <h1>Créer un compte</h1>
    <form method="POST">
        <label>Prénom :</label>
        <input type="text" name="prenom" required>

        <br><br>

        <label>Nom :</label>
        <input type="text" name="nom" required>

        <br><br>

        <label>Courriel :</label>
        <input type="email" name="courriel" required>

        <br><br>

        <label>Mot de passe :</label>
        <input type="password" name="mot_de_passe" required>

        <br><br>

        <button type="submit">Créer mon compte</button>


    </form>

    <a href="connexion.php">Retour à la connexion</a>
</body>
</html>