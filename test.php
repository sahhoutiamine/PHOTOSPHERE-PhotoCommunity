<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Services/UserFactory.php';
require_once __DIR__ . '/Repositories/UserRepository.php';
require_once __DIR__ . '/Repositories/AlbumRepository.php';
require_once __DIR__ . '/Repositories/TagRepository.php';
require_once __DIR__ . '/Models/Photo.php';

function printTest(string $name, $result, $message = '') {
    $status = $result ? "[PASS]" : "[FAIL]";
    $color = $result ? "\033[32m" : "\033[31m";
    $reset = "\033[0m";
    echo "{$color}{$status} {$name}{$reset}\n";
    if (!empty($message)) {
        echo "       Details: {$message}\n";
    }
    echo "----------------------------------------\n";
}

function cleanupDatabase(PDO $db) {
    try {
        $db->exec("SET FOREIGN_KEY_CHECKS = 0");
        $db->exec("TRUNCATE TABLE photo_tags");
        $db->exec("TRUNCATE TABLE tags");
        $db->exec("TRUNCATE TABLE photos");
        $db->exec("TRUNCATE TABLE albums");
        $db->exec("TRUNCATE TABLE users");
        $db->exec("SET FOREIGN_KEY_CHECKS = 1");
        echo "Database cleaned up successfully.\n\n";
    } catch (PDOException $e) {
        die("Cleanup failed: " . $e->getMessage());
    }
}

function manuallyCreatePhoto(PDO $db, int $userId, string $title, string $state = 'published'): int {
    $stmt = $db->prepare("
        INSERT INTO photos (title, description, imageLink, state, createdAt, userId) 
        VALUES (?, 'Test Description', 'https://placehold.co/600x400', ?, NOW(), ?)
    ");
    $stmt->execute([$title, $state, $userId]);
    return (int)$db->lastInsertId();
}

try {
    echo "========================================\n";
    echo "    PHOTOSPHERE SYSTEM TEST SUITE       \n";
    echo "========================================\n\n";

    $db = Database::getInstance()->getConnection();
    cleanupDatabase($db);

    echo "\n>>> TESTING USER MANAGEMENT\n";

    $userRepo = new UserRepository();

    $basicUserData = [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password123',
        'bio' => 'A tester account',
        'role' => 'BasicUser'
    ];
    $basicUser = $userRepo->create($basicUserData);
    printTest("Create Basic User", $basicUser instanceof BasicUser && $basicUser->getId() > 0, "ID: " . $basicUser->getId());
    $userId = $basicUser->getId();

    $foundUser = $userRepo->findById($userId);
    printTest("Find User By ID", $foundUser && $foundUser->getUsername() === 'testuser');

    $foundByEmail = $userRepo->findByEmail('test@example.com');
    printTest("Find User By Email", $foundByEmail && $foundByEmail->getId() === $userId);

    $basicUser->setBio("Updated Bio");
    $updateResult = $userRepo->update($basicUser);
    $reloadedUser = $userRepo->findById($userId);
    printTest("Update User Bio", $updateResult && $reloadedUser->getBio() === "Updated Bio");

    $authUser = $userRepo->authenticate('test@example.com', 'password123');
    printTest("Authenticate User (Correct)", $authUser !== null);
    
    $failUser = $userRepo->authenticate('test@example.com', 'wrongpassword');
    printTest("Authenticate User (Wrong Password)", $failUser === null);

    $proUser = $userRepo->create([
        'username' => 'prouser', 
        'email' => 'pro@example.com', 
        'password' => 'secret',
        'role' => 'ProUser',
        'subscriptionStart' => date('Y-m-d H:i:s'),
        'subscriptionEnd' => date('Y-m-d H:i:s', strtotime('+1 year'))
    ]);
    printTest("Create Pro User", $proUser instanceof ProUser, "ID: " . $proUser->getId());
    $proUserId = $proUser->getId();
    
    $adminUser = $userRepo->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => 'adminpass',
        'role' => 'Administrator',
        'isSuper' => true
    ]);
    printTest("Create Administrator", $adminUser instanceof Administrator, "ID: " . $adminUser->getId());

    echo "\n>>> TESTING ALBUM MANAGEMENT\n";

    $albumRepo = new AlbumRepository();

    try {
        $albumId = $albumRepo->createAlbum($userId, "My Vacation", "cover.jpg", false);
        printTest("Create Public Album (Basic User)", $albumId > 0, "AlbumID: $albumId");
    } catch (Exception $e) {
        printTest("Create Public Album (Basic User)", false, $e->getMessage());
    }

    try {
        $albumRepo->createAlbum($userId, "Secret Album", "cover.jpg", true);
        printTest("Create Private Album (Basic User - Should Fail)", false, "Exception expected but not thrown");
    } catch (Exception $e) {
        printTest("Create Private Album (Basic User - Should Fail)", true, "Caught expected error: " . $e->getMessage());
    }

    try {
        $privAlbumId = $albumRepo->createAlbum($proUserId, "Pro Secret", "secret.jpg", true);
        printTest("Create Private Album (Pro User)", $privAlbumId > 0, "AlbumID: $privAlbumId");
    } catch (Exception $e) {
        printTest("Create Private Album (Pro User)", false, $e->getMessage());
    }

    $photoId = manuallyCreatePhoto($db, $userId, "Sunset Beach");
    
    try {
        $added = $albumRepo->addPhotoToAlbum($albumId, $photoId, $userId);
        printTest("Add Photo to Album", $added);
    } catch (Exception $e) {
        printTest("Add Photo to Album", false, $e->getMessage());
    }

    $albumData = $albumRepo->getAlbumWithPhotos($albumId, $userId);
    $hasPhotos = isset($albumData['photos']) && count($albumData['photos']) > 0;
    printTest("Get Album Content", $hasPhotos, "Photo count: " . ($hasPhotos ? count($albumData['photos']) : 0));

    try {
        $updateAlbumResult = $albumRepo->updateAlbum($albumId, $userId, ['name' => 'Updated Vacation Title']);
        printTest("Update Album Title", $updateAlbumResult);
        $updatedAlbum = $albumRepo->getAlbumWithPhotos($albumId, $userId);
        printTest("Verify Album Update", $updatedAlbum['album']['name'] === 'Updated Vacation Title');
    } catch (Exception $e) {
        printTest("Update Album", false, $e->getMessage());
    }

    try {
        $removeResult = $albumRepo->removePhotoFromAlbum($albumId, $photoId, $userId);
        printTest("Remove Photo from Album", $removeResult);
        $checkAlbum = $albumRepo->getAlbumWithPhotos($albumId, $userId);
        $photoStillThere = false;
        foreach ($checkAlbum['photos'] as $p) {
            if ($p['id'] == $photoId) $photoStillThere = true;
        }
        printTest("Verify Photo Removal", !$photoStillThere);
        
        $albumRepo->addPhotoToAlbum($albumId, $photoId, $userId);
    } catch (Exception $e) {
        printTest("Remove Photo from Album", false, $e->getMessage());
    }

    $userAlbums = $albumRepo->getUserAlbums($userId);
    printTest("Get User Albums List", isset($userAlbums['albums']) && count($userAlbums['albums']) >= 1);

    $exists = $albumRepo->albumExists($albumId);
    printTest("Album Exists Check", $exists);

    $stats = $albumRepo->getAlbumStats($albumId);
    printTest("Get Album Stats", is_array($stats));

    try {
        $dummyAlbumId = $albumRepo->createAlbum($userId, "Delete Me", "cover.jpg", false);
        $deleteKwResult = $albumRepo->deleteAlbum($dummyAlbumId, $userId);
        printTest("Delete Album", $deleteKwResult);
        
        $shouldNotExist = $albumRepo->albumExists($dummyAlbumId);
        printTest("Verify Album Deleted", !$shouldNotExist);
    } catch (Exception $e) {
        printTest("Delete Album", false, $e->getMessage());
    }

    echo "\n>>> TESTING TAG MANAGEMENT\n";
    
    $tagRepo = new TagRepository();

    $db->prepare("INSERT INTO tags (slug, photoCount) VALUES ('sunset', 1), ('nature', 0), ('beach', 5)")->execute();
    $tagId = $db->lastInsertId();
    
    $stmt = $db->prepare("SELECT id FROM tags WHERE slug = 'sunset'");
    $stmt->execute();
    $sunsetTag = $stmt->fetch();
    $stmt = $db->prepare("SELECT id FROM tags WHERE slug = 'nature'");
    $stmt->execute();
    $natureTag = $stmt->fetch();

    if ($sunsetTag) {
        $db->prepare("INSERT INTO photo_tags (photoId, tagId) VALUES (?, ?)")->execute([$photoId, $sunsetTag['id']]);
        printTest("Setup: Manually linked 'sunset' tag to photo", true);
    }

    $searchResults = $tagRepo->searchTags('sun');
    printTest("Search Tags ('sun')", count($searchResults) > 0 && $searchResults[0]->getSlug() === 'sunset');

    $popularTags = $tagRepo->getPopularTags(10);
    printTest("Get Popular Tags", count($popularTags) > 0);

    $taggedPhotos = $tagRepo->getPhotosByTag('sunset');
    printTest("Get Photos by Tag ('sunset')", count($taggedPhotos) > 0, "Found " . count($taggedPhotos) . " photos");

    $mergeResult = $tagRepo->mergeTags('sunset', 'nature');
    printTest("Merge Tags (sunset -> nature)", $mergeResult);
    
    $natureStats = $tagRepo->getTagStats('nature');
    printTest("Verify Merge Result", $natureStats['totalPhotos'] == 1, "Nature tag photos: " . $natureStats['totalPhotos']);

    echo "\n>>> TEARDOWN\n";
    
    $allUsers = $userRepo->findAll();
    printTest("Find All Users", count($allUsers) >= 2);
    
    $deleteResult = $userRepo->delete($userId);
    printTest("Delete Basic User", $deleteResult);
    
    $deletePro = $userRepo->delete($proUserId);
    printTest("Delete Pro User", $deletePro);

    $findDeleted = $userRepo->findById($userId);
    printTest("Verify User Deleted", $findDeleted === null);

} catch (Exception $e) {
    echo "\n\033[31m[CRITICAL ERROR]\033[0m Test suite halted: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}