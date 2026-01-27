<?php
session_start();
$host = getenv("DB_HOST") ?: "db";
$db   = getenv("DB_NAME") ?: "app";
$user = getenv("DB_USER") ?: "app";
$pass = getenv("DB_PASS") ?: "apppass";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
} catch (Exception $e) {
    echo "<p>Erreur: " . $e->getMessage() . "</p>";
}

require_once __DIR__ . '/migrate.php';
runMigrations($pdo);

// Traiter le formulaire AVANT d'envoyer le HTML
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
        $_SESSION['result'] = $result;
        $pdo->prepare("INSERT INTO results (resultat) VALUES (:resultat)")
            ->execute(['resultat' => $result]);

        // Redirection pour éviter la resoumission au refresh
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    } else {
        $_SESSION['result'] = 'Aucun champ rempli.';
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// Récupérer le résultat de la session s'il existe
if (isset($_SESSION['result'])) {
    $result = $_SESSION['result'];
    unset($_SESSION['result']); // Effacer après utilisation
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Applicative</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="meteorites-container" id="meteoritesContainer"></div>
    <div class="container">
        <div class="header">
            <h1>Maintenance Applicative</h1>
        </div>
        <div class="content">
            <p>Liste des développeurs :</p>
            <ul>
                <li>Enzo VANDEPOELE</li>
                <li>Mathieu DUCROT (le créateur du front)</li>
                <li>Léo HENRIOT</li>
            </ul>

            <p>Page de tirage aléatoire !</p>

            <form method="post" id="mainForm">
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <input type="text" name="input<?php echo $i; ?>" placeholder="Champ <?php echo $i; ?>" value="<?php echo htmlspecialchars($_POST["input$i"] ?? ''); ?>"><br><br>
                <?php endfor; ?>
                <button type="submit" id="submitBtn">Tirer au sort</button>
            </form>

            <?php if ($result): ?>
                <p>Résultat du tirage : <strong><?php echo htmlspecialchars($result); ?></strong></p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const container = document.getElementById('meteoritesContainer');
        const colors = ['#ffff00', '#ff7f00', '#ff0000', '#ffcc00', '#ff4500'];

        // Troll du bouton
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('mainForm');
        let clickCount = 0;

        submitBtn.addEventListener('click', (e) => {
            clickCount++;

            if (clickCount < 3) {
                e.preventDefault();
                // Déplacer le bouton aléatoirement
                const randomX = Math.random() * (window.innerWidth - 150);
                const randomY = Math.random() * (window.innerHeight - 50);

                submitBtn.style.position = 'fixed';
                submitBtn.style.left = randomX + 'px';
                submitBtn.style.top = randomY + 'px';
                submitBtn.style.zIndex = '100';
            }
            // Au 3e clic, on laisse le formulaire se soumettre naturellement
        });

        function createMeteor() {
            const meteor = document.createElement('div');
            meteor.className = 'meteorite';
            const startX = Math.random() * window.innerWidth;
            const duration = 2 + Math.random() * 3;
            const angle = -45 + Math.random() * 30;

            meteor.style.left = startX + 'px';
            meteor.style.top = '-10px';
            meteor.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            meteor.style.animation = `meteoriteFall ${duration}s linear forwards`;

            container.appendChild(meteor);

            // Création d'une explosion à la fin
            setTimeout(() => {
                const rect = meteor.getBoundingClientRect();
                createExplosion(rect.left, rect.top);
                meteor.remove();
            }, duration * 1000);
        }

        function createExplosion(x, y) {
            const explosion = document.createElement('div');
            explosion.className = 'explosion';
            explosion.style.left = x + 'px';
            explosion.style.top = y + 'px';
            container.appendChild(explosion);

            // Onde de choc
            const shockwave = document.createElement('div');
            shockwave.className = 'explosion-shockwave';
            explosion.appendChild(shockwave);

            const particleCount = 36;
            const colors_exp = ['#ff0000', '#ff4500', '#ff7f00', '#ffff00', '#ffa500', '#ff6347'];

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'explosion-particle';

                const angle = (i / particleCount) * Math.PI * 2 + (Math.random() - 0.5) * 0.3;
                const velocity = 200 + Math.random() * 300;
                const tx = Math.cos(angle) * velocity;
                const ty = Math.sin(angle) * velocity;

                particle.style.backgroundColor = colors_exp[Math.floor(Math.random() * colors_exp.length)];
                particle.style.setProperty('--tx', tx + 'px');
                particle.style.setProperty('--ty', ty + 'px');
                particle.style.animation = `particleExplosion 1s ease-out forwards`;

                explosion.appendChild(particle);
            }

            setTimeout(() => explosion.remove(), 1000);
        }

        // Lancer une météorite toutes les 50 à 150 millisecondes - BOMBARDEMENT INTENSIF !
        setInterval(createMeteor, 50 + Math.random() * 100);

        // Créer un DÉLUGE de météorites au chargement
        for (let i = 0; i < 25; i++) {
            setTimeout(() => createMeteor(), i * 30);
        }

        // Bonus: créer des vagues de météorites supplémentaires
        setInterval(() => {
            for (let i = 0; i < 5; i++) {
                setTimeout(() => createMeteor(), i * 20);
            }
        }, 2000);

        // Nyan-Cat popup toutes les 4 secondes
        function showNyanCat() {
            const nyancat = document.createElement('img');
            nyancat.src = 'nyan-cat.gif';
            nyancat.style.position = 'fixed';
            nyancat.style.zIndex = '1000';
            nyancat.style.pointerEvents = 'none';
            nyancat.style.width = '400px';
            nyancat.style.height = 'auto';

            // Position aléatoire
            const randomX = Math.random() * (window.innerWidth - 400);
            const randomY = Math.random() * (window.innerHeight - 300);
            nyancat.style.left = randomX + 'px';
            nyancat.style.top = randomY + 'px';

            document.body.appendChild(nyancat);

            // Disparaître après 0.5 secondes
            setTimeout(() => {
                nyancat.remove();
            }, 500);
        }

        // Lancer Nyan-Cat toutes les 2.5 secondes
        setInterval(showNyanCat, 2500);
    </script>
</body>


</html>