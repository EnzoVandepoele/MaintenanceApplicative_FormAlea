<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Maintenance Applicative</title>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Maintenance Applicative</h1>
            </div>
            <div class="content">
                <p>Liste des développeurs :</p>
                <ul>
                    <li>Enzo VANDEPOELE</li>
                </ul>

                <p>Page de tirage aléatoire !</p>

                <?php
                    $result = '';
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $inputs = [];
                        for ($i = 1; $i <= 10; $i++) {
                            $value = trim($_POST["input$i"] ?? '');
                            if (!empty($value)) {
                                $inputs[] = $value;
                            }
                        }
                        if (!empty($inputs)) {
                            $randomIndex = array_rand($inputs);
                            $result = $inputs[$randomIndex];
                        } else {
                            $result = 'Aucun champ rempli.';
                        }
                    }
                ?>
                
                <form method="post">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <input type="text" name="input<?php echo $i; ?>" placeholder="Champ <?php echo $i; ?>" value="<?php echo htmlspecialchars($_POST["input$i"] ?? ''); ?>"><br><br>
                    <?php endfor; ?>
                    <button type="submit">Tirer au sort</button>
                </form>
                
                <?php if ($result): ?>
                    <p>Résultat du tirage : <strong><?php echo htmlspecialchars($result); ?></strong></p>
                <?php endif; ?>
            </div>
        </div>
    </body>
</html>