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
    <meta name="keywords" content="maintenance, applicative, tirage, aleatoire, cool, site, web, 2000, geocities">
    <meta name="description" content="Le meilleur site de tirage aleatoire du WEB!!!">
    <meta name="author" content="Enzo, Mathieu, Leo - WebMasters Certifiés">
    <title>~*~MaInTeNaNcE aPpLiCaTiVe~*~ - LE MEILLEUR SITE DU WEB!!!</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="meteorites-container" id="meteoritesContainer"></div>
    <img src="sebastien.jpeg" alt="Sebastien - Notre Mascotte!!!" id="sebastien" title="Ceci est Sebastien. Il est cool. Il est basé à creteil.">

    <div class="container">
        <div class="header">
            <h1>~*~MaInTeNaNcE~*~<br>aPpLiCaTiVe!!!</h1>
            <marquee behavior="alternate" scrollamount="3">
                <font color="red" size="4">★ Le site le plus COOL du web ★</font>
            </marquee>
        </div>
        <div class="content">
            <p>✨ LiStE dEs DéVeLoPpEuRs ✨</p>
            <ul>
                <li>Enzo VANDEPOELE</li>
                <li>Mathieu DUCROT (le créateur du front)</li>
                <li>Léo HENRIOT</li>
                <li>Firmin BORRACINO (le destructeur du front)</li>
            </ul>

            <hr>

            <marquee behavior="scroll" direction="right" scrollamount="2">
                <font color="blue" size="3">>>> NOUVEAU!!! Page de tirage aléatoire!!! Essayez maintenant!!! <<<< /font>
            </marquee>

            <hr>

            <p style="font-size: 22px; text-align: center;">
                <blink>🎲 PAGE DE TIRAGE ALÉATOIRE 🎲</blink>
            </p>

            <form method="post" id="mainForm">
                <table class="form-table">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <tr>
                            <td style="width: 30%; color: #800080; font-weight: bold;">
                                ➤ Champ <?php echo $i; ?>:
                            </td>
                            <td>
                                <input type="text" name="input<?php echo $i; ?>" placeholder="Entrez la valeur <?php echo $i; ?> ici!!!" value="<?php echo htmlspecialchars($_POST["input$i"] ?? ''); ?>">
                            </td>
                        </tr>
                    <?php endfor; ?>
                </table>
                <button type="submit" id="submitBtn">🎰 TIRER AU SORT!!! 🎰</button>
            </form>

            <?php if ($result): ?>
                <hr>
                <p style="text-align: center; font-size: 20px;">
                    🎉🎊 RÉSULTAT DU TIRAGE 🎊🎉<br><br>
                    <strong><?php echo htmlspecialchars($result); ?></strong>
                </p>
                <marquee>
                    <font color="green" size="4">*** FÉLICITATIONS!!! ***</font>
                </marquee>
            <?php endif; ?>

            <hr>

            <div class="email-link">
                📧 <a href="mailto:webmaster@maintenance-applicative.geocities.com">CONTACTEZ LE WEBMASTER!!!</a> 📧
            </div>

            <div class="best-viewed">
                🖥️ Best viewed with Netscape Navigator 4.0 at 800x600 resolution 🖥️<br>
                <small>© 1999-<?php echo date('Y'); ?> - All Rights Reserved - Made with ❤️ and MS FrontPage</small>
            </div>

            <div class="webring">
                <strong>🔗 WEBRING - Sites de tirage aléatoire 🔗</strong><br>
                <a href="#">&lt;&lt; Précédent</a> |
                <a href="#">Liste des sites</a> |
                <a href="#">Aléatoire</a> |
                <a href="#">Suivant &gt;&gt;</a>
            </div>
        </div>
    </div>

    <script>
        const container = document.getElementById('meteoritesContainer');
        const colors = ['#ffff00', '#ff00ff', '#00ffff', '#ff0000', '#00ff00', '#ff7f00'];

        // Troll du bouton - AMELIORÉ
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('mainForm');
        let clickCount = 0;

        submitBtn.addEventListener('click', (e) => {
            clickCount++;

            if (clickCount < 5) { // Encore plus de trollage
                e.preventDefault();
                // Déplacer le bouton aléatoirement
                const randomX = Math.random() * (window.innerWidth - 200);
                const randomY = Math.random() * (window.innerHeight - 100);

                submitBtn.style.position = 'fixed';
                submitBtn.style.left = randomX + 'px';
                submitBtn.style.top = randomY + 'px';
                submitBtn.style.zIndex = '100';

                // Messages aléatoires
                const messages = [
                    "LOL TU M'AURAS PAS!!!",
                    "TROP LENT!!!",
                    "ESSAIE ENCORE!!!",
                    "PRESQUE!!!"
                ];
                submitBtn.textContent = messages[clickCount - 1] || "🎰 TIRER AU SORT!!! 🎰";
            } else {
                submitBtn.textContent = "OK TU GAGNES...";
            }
        });

        function createMeteor() {
            const meteor = document.createElement('div');
            meteor.className = 'meteorite';
            const startX = Math.random() * window.innerWidth;
            const duration = 1.5 + Math.random() * 2;

            meteor.style.left = startX + 'px';
            meteor.style.top = '-20px';
            meteor.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            meteor.style.animation = `meteoriteFall ${duration}s linear forwards`;

            container.appendChild(meteor);

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

            const shockwave = document.createElement('div');
            shockwave.className = 'explosion-shockwave';
            explosion.appendChild(shockwave);

            const particleCount = 20;
            const colors_exp = ['#ff0000', '#ff00ff', '#00ffff', '#ffff00', '#00ff00'];

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'explosion-particle';

                const angle = (i / particleCount) * Math.PI * 2 + (Math.random() - 0.5) * 0.5;
                const velocity = 250 + Math.random() * 350;
                const tx = Math.cos(angle) * velocity;
                const ty = Math.sin(angle) * velocity;

                particle.style.backgroundColor = colors_exp[Math.floor(Math.random() * colors_exp.length)];
                particle.style.setProperty('--tx', tx + 'px');
                particle.style.setProperty('--ty', ty + 'px');
                particle.style.animation = `particleExplosion 1.2s ease-out forwards`;

                explosion.appendChild(particle);
            }

            setTimeout(() => explosion.remove(), 1200);
        }

        // BOMBARDEMENT INTENSE
        setInterval(createMeteor, 400 + Math.random() * 800);

        // Déluge initial
        for (let i = 0; i < 25; i++) {
            setTimeout(() => createMeteor(), i * 50);
        }

        // Vagues bonus
        setInterval(() => {
            for (let i = 0; i < 5; i++) {
                setTimeout(() => createMeteor(), i * 30);
            }
        }, 5000);

        // Nyan-Cat popup - PLUS FRÉQUENT
        function showNyanCat() {
            const nyancat = document.createElement('img');
            nyancat.src = 'nyan-cat.gif';
            nyancat.style.position = 'fixed';
            nyancat.style.zIndex = '1000';
            nyancat.style.pointerEvents = 'none';
            nyancat.style.width = '300px';
            nyancat.style.height = 'auto';
            nyancat.style.border = '5px ridge #ff00ff';

            const randomX = Math.random() * (window.innerWidth - 300);
            const randomY = Math.random() * (window.innerHeight - 200);
            nyancat.style.left = randomX + 'px';
            nyancat.style.top = randomY + 'px';

            document.body.appendChild(nyancat);

            setTimeout(() => nyancat.remove(), 800);
        }

        setInterval(showNyanCat, 1500);

        // ALERT DE BIENVENUE
        if (!sessionStorage.getItem('welcomed')) {
            setTimeout(() => {
                alert("🎉 BIENVENUE SUR LE MEILLEUR SITE DU WEB!!! 🎉\n\nN'oubliez pas de:\n- Signer le guestbook\n- Ajouter ce site à vos favoris\n- Revenir TOUS LES JOURS!!!\n\n© 1999 - WebMaster Enzo");
                sessionStorage.setItem('welcomed', 'true');
            }, 1000);
        }

        // STATUS BAR MESSAGE
        let statusMessages = [
            "Bienvenue sur le meilleur site!!!",
            "N'oubliez pas le guestbook!!!",
            "Site optimisé pour Netscape!!!",
            "Revenez demain pour du nouveau contenu!!!",
            "Ajoutez ce site à vos favoris!!!"
        ];
        let msgIndex = 0;
        setInterval(() => {
            window.status = statusMessages[msgIndex];
            msgIndex = (msgIndex + 1) % statusMessages.length;
        }, 3000);
    </script>
</body>

</html>