<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Crï¿½ï¿½</title>
    <meta http-equiv="refresh" content="5;url=login"> <!-- Redirection aprï¿½s 5 secondes -->
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 50px;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <?php echo "<h2>Votre compte a été créé avec succès $username!</h2>" ?>
        <p>Vous serez redirigé vers la page de connexion dans 5 secondes...</p>
        <p>Si la redirection ne fonctionne pas, cliquez <a href="login">ici</a>.</p>
    </div>
</body>
</html>
