<?php
// Enable error reporting for testing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Services/UserFactory.php';
require_once __DIR__ . '/Repositories/UserRepository.php';
require_once __DIR__ . '/Repositories/AlbumRepository.php';
require_once __DIR__ . '/Repositories/TagRepository.php';
require_once __DIR__ . '/Models/Photo.php';

// Helper function to print results
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

// Database Helper for Cleanup
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

// Global Manual Insert Helper for Photos (Since PhotoRepository is missing)
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

    // 1. Setup
    $db = Database::getInstance()->getConnection();
    cleanupDatabase($db);

    // ==========================================
    // SECTION 1: USER REPOSITORY & FACTORY
    // ==========================================
    echo "\n>>> TESTING USER MANAGEMENT\n";

    $userRepo = new UserRepository();

    // Test 1.1: Create Basic User
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

    // Test 1.2: Find By ID
    $foundUser = $userRepo->findById($userId);
    printTest("Find User By ID", $foundUser && $foundUser->getUsername() === 'testuser');

    // Test 1.3: Find By Email
    $foundByEmail = $userRepo->findByEmail('test@example.com');
    printTest("Find User By Email", $foundByEmail && $foundByEmail->getId() === $userId);

    // Test 1.4: Update User
    $basicUser->setBio("Updated Bio");
    $updateResult = $userRepo->update($basicUser);
    $reloadedUser = $userRepo->findById($userId);
    printTest("Update User Bio", $updateResult && $reloadedUser->getBio() === "Updated Bio");

    // Test 1.5: Authenticate
    $authUser = $userRepo->authenticate('test@example.com', 'password123');
    printTest("Authenticate User (Correct)", $authUser !== null);
    
    $failUser = $userRepo->authenticate('test@example.com', 'wrongpassword');
    printTest("Authenticate User (Wrong Password)", $failUser === null);

    // Create Pro User for Album Tests
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
    
    // Test 1.6: Create Administrator
    $adminUser = $userRepo->create([
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password' => 'adminpass',
        'role' => 'Administrator',
        'isSuper' => true
    ]);
    printTest("Create Administrator", $adminUser instanceof Administrator, "ID: " . $adminUser->getId());


    // ==========================================
    // SECTION 2: ALBUM REPOSITORY
    // ==========================================
    echo "\n>>> TESTING ALBUM MANAGEMENT\n";

    $albumRepo = new AlbumRepository();

    // Test 2.1: Create Public Album
    try {
        $albumId = $albumRepo->createAlbum($userId, "My Vacation", "cover.jpg", false);
        printTest("Create Public Album (Basic User)", $albumId > 0, "AlbumID: $albumId");
    } catch (Exception $e) {
        printTest("Create Public Album (Basic User)", false, $e->getMessage());
    }

    // Test 2.2: Create Private Album (Basic User - Should Fail/Check Logic)
    // Note: implementation says Pro can create private.
    try {
        $albumRepo->createAlbum($userId, "Secret Album", "cover.jpg", true);
        printTest("Create Private Album (Basic User - Should Fail)", false, "Exception expected but not thrown");
    } catch (Exception $e) {
        printTest("Create Private Album (Basic User - Should Fail)", true, "Caught expected error: " . $e->getMessage());
    }

    // Test 2.3: Create Private Album (Pro User)
    try {
        $privAlbumId = $albumRepo->createAlbum($proUserId, "Pro Secret", "secret.jpg", true);
        printTest("Create Private Album (Pro User)", $privAlbumId > 0, "AlbumID: $privAlbumId");
    } catch (Exception $e) {
        printTest("Create Private Album (Pro User)", false, $e->getMessage());
    }

    // Test 2.4: Add Photo to Album
    // First, manually Create a Photo for the User
    $photoId = manuallyCreatePhoto($db, $userId, "Sunset Beach");
    
    try {
        $added = $albumRepo->addPhotoToAlbum($albumId, $photoId, $userId);
        printTest("Add Photo to Album", $added);
    } catch (Exception $e) {
        printTest("Add Photo to Album", false, $e->getMessage());
    }

    // Test 2.5: Get Album With Photos
    $albumData = $albumRepo->getAlbumWithPhotos($albumId, $userId);
    $hasPhotos = isset($albumData['photos']) && count($albumData['photos']) > 0;
    printTest("Get Album Content", $hasPhotos, "Photo count: " . ($hasPhotos ? count($albumData['photos']) : 0));

    // Test 2.5b: Update Album
    try {
        $updateAlbumResult = $albumRepo->updateAlbum($albumId, $userId, ['name' => 'Updated Vacation Title']);
        printTest("Update Album Title", $updateAlbumResult);
        $updatedAlbum = $albumRepo->getAlbumWithPhotos($albumId, $userId);
        printTest("Verify Album Update", $updatedAlbum['album']['name'] === 'Updated Vacation Title');
    } catch (Exception $e) {
        printTest("Update Album", false, $e->getMessage());
    }

    // Test 2.5c: Remove Photo from Album
    try {
        $removeResult = $albumRepo->removePhotoFromAlbum($albumId, $photoId, $userId);
        printTest("Remove Photo from Album", $removeResult);
        // Verify removal
        $checkAlbum = $albumRepo->getAlbumWithPhotos($albumId, $userId);
        $photoStillThere = false;
        foreach ($checkAlbum['photos'] as $p) {
            if ($p['id'] == $photoId) $photoStillThere = true;
        }
        printTest("Verify Photo Removal", !$photoStillThere);
        
        // Add it back for Tag tests
        $albumRepo->addPhotoToAlbum($albumId, $photoId, $userId);
    } catch (Exception $e) {
        printTest("Remove Photo from Album", false, $e->getMessage());
    }

    // Test 2.6: Get User Albums
    $userAlbums = $albumRepo->getUserAlbums($userId);
    // Note: getUserAlbums returns an array with 'albums' key
    printTest("Get User Albums List", isset($userAlbums['albums']) && count($userAlbums['albums']) >= 1);

    // Test 2.7: Album Exists
    $exists = $albumRepo->albumExists($albumId);
    printTest("Album Exists Check", $exists);

    // Test 2.8: Get Album Stats
    $stats = $albumRepo->getAlbumStats($albumId);
    // Depending on what stats return, at least check if array
    printTest("Get Album Stats", is_array($stats));

    // Test 2.9: Delete Album
    // We'll assume $albumId is the one we created.
    // Note: This must be done carefuly if other tests rely on it, but we are near end of Album section.
    // However, Section 3 uses $photoId which is in $albumId?
    // Wait, in Section 2.5c we removed the photo, then added it back.
    // Section 3 uses $photoId which is a raw photo, but linked to tags.
    // If we delete the album, the photo might be affected if cascading delete?
    // Let's check DB schema or just assume safe since 'photos' usually stay or set NULL.
    // In AlbumRepository::deleteAlbum, it deletes from 'albums'.
    // If FK constraint with CASCADE, photos disappear.
    // Let's create a dummy album for deletion test to be safe.
    try {
        $dummyAlbumId = $albumRepo->createAlbum($userId, "Delete Me", "cover.jpg", false);
        $deleteKwResult = $albumRepo->deleteAlbum($dummyAlbumId, $userId);
        printTest("Delete Album", $deleteKwResult);
        
        $shouldNotExist = $albumRepo->albumExists($dummyAlbumId);
        printTest("Verify Album Deleted", !$shouldNotExist);
    } catch (Exception $e) {
        printTest("Delete Album", false, $e->getMessage());
    }


    // ==========================================
    // SECTION 3: TAG REPOSITORY
    // ==========================================
    echo "\n>>> TESTING TAG MANAGEMENT\n";
    
    $tagRepo = new TagRepository();

    // Test 3.1: Create and Associate Tags (Requires Manual Setup since TagRepo only reads/merges primarily)
    // We need to insert a tag and link it to the photo manually strictly for testing 'getPhotosByTag'
    
    // Insert Tags
    $db->prepare("INSERT INTO tags (slug, photoCount) VALUES ('sunset', 1), ('nature', 0), ('beach', 5)")->execute();
    $tagId = $db->lastInsertId();
    
    // Link Tag to Photo
    // First get the tag ID properly if multiple inserts
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

    // Test 3.2: Search Tags
    $searchResults = $tagRepo->searchTags('sun');
    printTest("Search Tags ('sun')", count($searchResults) > 0 && $searchResults[0]->getSlug() === 'sunset');

    // Test 3.2b: Get Popular Tags
    $popularTags = $tagRepo->getPopularTags(10);
    printTest("Get Popular Tags", count($popularTags) > 0);
    // 'beach' has 5 photos (fake count inserted), so it should be top if we inserted it right.

    // Test 3.3: Get Photos by Tag
    $taggedPhotos = $tagRepo->getPhotosByTag('sunset');
    printTest("Get Photos by Tag ('sunset')", count($taggedPhotos) > 0, "Found " . count($taggedPhotos) . " photos");

    // Test 3.4: Merge Tags (Merge 'sunset' into 'nature')
    // 'sunset' has 1 photo, 'nature' has 0. After merge, 'nature' should have 1 photo.
    $mergeResult = $tagRepo->mergeTags('sunset', 'nature');
    printTest("Merge Tags (sunset -> nature)", $mergeResult);
    
    $natureStats = $tagRepo->getTagStats('nature');
    printTest("Verify Merge Result", $natureStats['totalPhotos'] == 1, "Nature tag photos: " . $natureStats['totalPhotos']);


    // ==========================================
    // FINAL CLEANUP check
    // ==========================================
    echo "\n>>> TEARDOWN\n";
    
    // Test 1.6: Find All Users (Adding here to clean up logic or just test)
    $allUsers = $userRepo->findAll();
    printTest("Find All Users", count($allUsers) >= 2); // basic + pro
    
    // Delete User
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