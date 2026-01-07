<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Repositories/UserRepository.php';
require_once __DIR__ . '/Repositories/AlbumRepository.php';
require_once __DIR__ . '/Repositories/TagRepository.php';

// Helper for output formatting
function printHeader($title) {
    echo "\n\033[1;34m" . str_repeat("=", 50) . "\n";
    echo " " . strtoupper($title) . "\n";
    echo str_repeat("=", 50) . "\033[0m\n";
}

function printStep($step, $description) {
    echo "\n\033[1;32m[Step $step]\033[0m $description\n";
}

function printInfo($info) {
    echo "  -> $info\n";
}




function createPhoto(PDO $db, int $userId, string $title, string $description) {
    $stmt = $db->prepare("
        INSERT INTO photos (title, description, imageLink, state, createdAt, userId) 
        VALUES (?, ?, 'https://placehold.co/600x400', 'published', NOW(), ?)
    ");
    $stmt->execute([$title, $description, $userId]);
    return (int)$db->lastInsertId();
}

// Helper to clean DB for fresh demo
function resetDatabase(PDO $db) {
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    $tables = ['photo_tags', 'tags', 'photos', 'albums', 'users'];
    foreach ($tables as $table) {
        $db->exec("TRUNCATE TABLE $table");
    }
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
}

try {
    $db = Database::getInstance()->getConnection();
    
    printHeader("SCÉNARIO DE DÉMONSTRATION PHOTOSPHERE");
    
    // 0. Reset
    printStep(0, "Initialisation de la base de données");
    resetDatabase($db);
    printInfo("Base de données nettoyée pour la démo.");

    // Repository Instantiation
    $userRepo = new UserRepository();
    $albumRepo = new AlbumRepository();
    $tagRepo = new TagRepository();

    // ---------------------------------------------------------
    // CASE 1: Alice (Basic User) Journey
    // ---------------------------------------------------------
    printHeader("SCÉNARIO 1: LE PARCOURS D'ALICE (UTILISATEUR STANDARD)");

    // 1. Inscription
    printStep(1, "Alice s'inscrit sur la plateforme");
    $alice = $userRepo->create([
        'username' => 'AliceWonder',
        'email' => 'alice@example.com',
        'password' => 'alicePass123',
        'role' => 'BasicUser'
    ]);
    printInfo("Utilisateur créé: " . $alice->getUsername() . " (Role: " . (new ReflectionClass($alice))->getShortName() . ")");

    // 2. Connexion
    printStep(2, "Alice se connecte");
    $loggedUser = $userRepo->authenticate('alice@example.com', 'alicePass123');
    if ($loggedUser) {
        printInfo("Authentification réussie pour " . $loggedUser->getUsername());
    }

    // 3. Création d'un album public
    printStep(3, "Alice crée un album public 'Vacances 2024'");
    $albumId = $albumRepo->createAlbum($alice->getId(), "Vacances 2024", "beach.jpg", false); // false = public
    printInfo("Album créé avec l'ID: $albumId");

    // 4. Upload de photo (Simulé)
    printStep(4, "Alice téléverse une photo 'Coucher de soleil'");
    $photoId = createPhoto($db, $alice->getId(), "Coucher de soleil", "Magnifique vue sur la mer");
    printInfo("Photo enregistrée avec l'ID: $photoId");

    // 5. Ajout de la photo à l'album
    printStep(5, "Elle ajoute la photo à son album");
    $albumRepo->addPhotoToAlbum($albumId, $photoId, $alice->getId());
    printInfo("Photo ajoutée à l'album.");

    // 6. Gestion des tags
    printStep(6, "Le système gère les tags pour la photo");
    // Manually adding tags for demo since logic might be in a Service/Controller usually
    // Let's say she tagged it "sunset" and "beach"
    $db->prepare("INSERT INTO tags (slug, photoCount) VALUES ('sunset', 1), ('beach', 1)")->execute();
    $tagId1 = $db->lastInsertId(); // beach (approx)
    $stmt = $db->query("SELECT id FROM tags WHERE slug='sunset'"); 
    $tagId2 = $stmt->fetchColumn();

    $db->prepare("INSERT INTO photo_tags (photoId, tagId) VALUES (?, ?)")->execute([$photoId, $tagId2]);
    printInfo("Tag 'sunset' associé à la photo.");

    // ---------------------------------------------------------
    // CASE 2: Bob (Pro User) Journey
    // ---------------------------------------------------------
    printHeader("SCÉNARIO 2: LE PARCOURS DE BOB (UTILISATEUR PRO)");

    // 1. Inscription Pro
    printStep(1, "Bob s'inscrit en tant que Pro");
    $bob = $userRepo->create([
        'username' => 'BobPro',
        'email' => 'bob@studio.com',
        'password' => 'proPass',
        'role' => 'ProUser'
    ]);
    printInfo("Utilisateur Pro créé: " . $bob->getUsername());

    // 2. Création d'un album PRIVÉ (Feature Pro)
    printStep(2, "Bob crée un album PRIVÉ 'Portfolio Clients'");
    try {
        $privateAlbumId = $albumRepo->createAlbum($bob->getId(), "Portfolio Clients", "secret_cover.jpg", true); // true = private
        printInfo("Album privé créé avec succès (ID: $privateAlbumId). Fonctionnalité PRO validée.");
    } catch (Exception $e) {
        printInfo("Erreur: " . $e->getMessage());
    }

    // Attempt by Alice to create private album (Should Fail)
    printStep(3, "Tentative d'Alice (Basic) de créer un album privé");
    try {
        $albumRepo->createAlbum($alice->getId(), "Secret Stuff", "cover.jpg", true);
        printInfo("ALERTE: Alice a réussi à créer un album privé (Inattendu).");
    } catch (Exception $e) {
        printInfo("Succès: Le système a bloqué Alice. Message: " . $e->getMessage());
    }

    // ---------------------------------------------------------
    // CASE 3: Community Interaction & Discovery
    // ---------------------------------------------------------
    printHeader("SCÉNARIO 3: DÉCOUVERTE ET INTERACTION");

    // 1. Recherche par tag
    printStep(1, "Un visiteur recherche des photos de 'sunset'");
    $photos = $tagRepo->getPhotosByTag('sunset');
    printInfo(count($photos) . " photo(s) trouvée(s) pour le tag 'sunset'.");

    // 2. Popular Tags
    printStep(2, "Affichage des tags populaires");
    $popular = $tagRepo->getPopularTags(5);
    foreach ($popular as $row) {
        printInfo("Tag '#" . $row['slug'] . "' a " . $row['photoCount'] . " photos."); // Assuming array return or object
    }

    printHeader("FIN DE LA DÉMONSTRATION");
    echo "Le script s'est exécuté sans erreurs critiques.\n";

} catch (Exception $e) {
    echo "\n\033[1;31m[ERREUR FATALE]\033[0m " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
