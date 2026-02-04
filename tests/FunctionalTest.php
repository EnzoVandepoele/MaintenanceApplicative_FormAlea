<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests fonctionnels pour l'application de tirage aléatoire.
 * 
 * Ces tests font de vraies requêtes HTTP vers le container Docker.
 * Le site doit tourner sur http://localhost:8080 (docker compose up).
 */
class FunctionalTest extends TestCase
{
    private const BASE_URL = 'http://localhost:8080';

    private $client;

    protected function setUp(): void
    {
        $this->client = HttpClient::create([
            'headers' => [
                'Accept' => 'text/html',
            ],
        ]);
    }

    /**
     * Test de chargement de la page d'accueil.
     * Vérifie que la page se charge correctement et contient les éléments attendus.
     */
    public function testPageLoadsSuccessfully(): void
    {
        $response = $this->client->request('GET', self::BASE_URL);

        // Vérifie le code HTTP 200
        $this->assertEquals(200, $response->getStatusCode(), 'La page doit retourner un code 200');

        $html = $response->getContent();
        $crawler = new Crawler($html);

        // Vérifie le titre de la page
        $this->assertEquals(
            'Maintenance Applicative',
            $crawler->filter('title')->text(),
            'Le titre doit être "Maintenance Applicative"'
        );

        // Vérifie que le formulaire existe
        $this->assertCount(1, $crawler->filter('form#mainForm'), 'Le formulaire principal doit exister');

        // Vérifie les 10 champs de saisie
        $inputs = $crawler->filter('form#mainForm input[type="text"]');
        $this->assertCount(10, $inputs, 'Le formulaire doit avoir 10 champs de saisie');

        // Vérifie le bouton de soumission
        $this->assertCount(1, $crawler->filter('button#submitBtn'), 'Le bouton "Tirer au sort" doit exister');

        // Vérifie la liste des développeurs
        $developers = $crawler->filter('.content ul li');
        $this->assertGreaterThanOrEqual(3, $developers->count(), 'La liste des développeurs doit contenir au moins 3 noms');
    }

    /**
     * Test de soumission du formulaire avec des valeurs.
     * Vérifie que le tirage aléatoire fonctionne et affiche un résultat.
     */
    public function testFormSubmissionWithValues(): void
    {
        // Prépare les données du formulaire avec quelques valeurs
        $formData = [
            'input1' => 'Alice',
            'input2' => 'Bob',
            'input3' => 'Charlie',
            'input4' => 'Sebastien',
            'input5' => '',
            'input6' => '',
            'input7' => '',
            'input8' => '',
            'input9' => '',
            'input10' => '',
        ];

        // Soumet le formulaire en POST
        $response = $this->client->request('POST', self::BASE_URL, [
            'body' => $formData,
            'max_redirects' => 0, // On veut voir la redirection
        ]);

        // Vérifie la redirection (PRG pattern)
        $this->assertEquals(302, $response->getStatusCode(), 'Le formulaire doit rediriger après soumission');

        // Suit la redirection avec les cookies de session
        $cookies = $response->getHeaders(false)['set-cookie'] ?? [];
        $cookieHeader = '';
        foreach ($cookies as $cookie) {
            if (preg_match('/^PHPSESSID=([^;]+)/', $cookie, $matches)) {
                $cookieHeader = 'PHPSESSID=' . $matches[1];
                break;
            }
        }

        // Récupère la page après redirection avec la session
        $response = $this->client->request('GET', self::BASE_URL, [
            'headers' => [
                'Cookie' => $cookieHeader,
            ],
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $html = $response->getContent();
        $crawler = new Crawler($html);

        // Vérifie qu'un résultat est affiché
        $resultText = $crawler->filter('.content p strong');
        $this->assertCount(1, $resultText, 'Un résultat doit être affiché après soumission');

        // Vérifie que le résultat est une des valeurs soumises
        $result = $resultText->text();
        $possibleValues = ['Alice', 'Bob', 'Charlie'];
        $this->assertContains($result, $possibleValues, "Le résultat '$result' doit être une des valeurs soumises");
    }

    /**
     * Test de soumission du formulaire sans valeurs.
     * Vérifie le comportement quand aucun champ n'est rempli.
     */
    public function testFormSubmissionWithoutValues(): void
    {
        // Soumet un formulaire vide
        $formData = [];
        for ($i = 1; $i <= 10; $i++) {
            $formData["input$i"] = '';
        }

        $response = $this->client->request('POST', self::BASE_URL, [
            'body' => $formData,
            'max_redirects' => 0,
        ]);

        // Vérifie la redirection
        $this->assertEquals(302, $response->getStatusCode(), 'Le formulaire vide doit aussi rediriger');

        // Suit la redirection avec la session
        $cookies = $response->getHeaders(false)['set-cookie'] ?? [];
        $cookieHeader = '';
        foreach ($cookies as $cookie) {
            if (preg_match('/^PHPSESSID=([^;]+)/', $cookie, $matches)) {
                $cookieHeader = 'PHPSESSID=' . $matches[1];
                break;
            }
        }

        $response = $this->client->request('GET', self::BASE_URL, [
            'headers' => [
                'Cookie' => $cookieHeader,
            ],
        ]);

        $html = $response->getContent();
        $crawler = new Crawler($html);

        // Vérifie le message d'erreur
        $resultText = $crawler->filter('.content p strong');
        $this->assertCount(1, $resultText, 'Un message doit être affiché');
        $this->assertEquals('Aucun champ rempli.', $resultText->text(), 'Le message doit indiquer qu\'aucun champ n\'est rempli');
    }

    /**
     * Test que les assets statiques sont accessibles.
     */
    public function testStaticAssetsAreAccessible(): void
    {
        // Test du CSS
        $response = $this->client->request('GET', self::BASE_URL . '/style.css');
        $this->assertEquals(200, $response->getStatusCode(), 'Le fichier CSS doit être accessible');
        $this->assertStringContainsString('text/css', $response->getHeaders()['content-type'][0]);
    }
}
