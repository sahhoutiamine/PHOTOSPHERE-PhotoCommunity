<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Models/BasicUser.php';
require_once __DIR__ . '/Models/ProUser.php';
require_once __DIR__ . '/Models/Moderator.php';
require_once __DIR__ . '/Models/Administrator.php';
require_once __DIR__ . '/Models/Photo.php';
require_once __DIR__ . '/Models/Album.php';
require_once __DIR__ . '/Models/Tag.php';
require_once __DIR__ . '/Models/Comment.php';
require_once __DIR__ . '/Models/Like.php';
require_once __DIR__ . '/Services/UserFactory.php';
require_once __DIR__ . '/Repositories/UserRepository.php';
require_once __DIR__ . '/Repositories/AlbumRepository.php';
require_once __DIR__ . '/Repositories/TagRepository.php';

function testDatabase() {
    echo "=== Testing Database ===\n";
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "Database connection: " . ($conn ? "SUCCESS" : "FAILED") . "\n";
    $db2 = Database::getInstance();
    echo "Singleton pattern: " . ($db === $db2 ? "SUCCESS" : "FAILED") . "\n\n";
}

function testUserFactory() {
    echo "=== Testing UserFactory ===\n";
    
    $basicUser = UserFactory::createBasicUser("basic_user", "basic@test.com", "password123");
    echo "BasicUser created: " . $basicUser->getUsername() . "\n";
    echo "Can upload: " . ($basicUser->canUploadPhoto() ? "YES" : "NO") . "\n";
    echo "Upload limit: " . $basicUser->getUploadLimit() . "\n";
    echo "Can create private album: " . ($basicUser->canCreatePrivateAlbum() ? "YES" : "NO") . "\n";
    echo "Remaining uploads: " . $basicUser->getRemainingUploads() . "\n";
    
    $proUser = UserFactory::createProUser("pro_user", "pro@test.com", "password123", "2026-12-31 23:59:59");
    echo "\nProUser created: " . $proUser->getUsername() . "\n";
    echo "Can upload: " . ($proUser->canUploadPhoto() ? "YES" : "NO") . "\n";
    echo "Upload limit: " . ($proUser->getUploadLimit() ?? "Unlimited") . "\n";
    echo "Can create private album: " . ($proUser->canCreatePrivateAlbum() ? "YES" : "NO") . "\n";
    echo "Has active subscription: " . ($proUser->hasActiveSubscription() ? "YES" : "NO") . "\n";
    
    $moderator = UserFactory::createModerator("mod_user", "mod@test.com", "password123", "senior");
    echo "\nModerator created: " . $moderator->getUsername() . "\n";
    echo "Can moderate: " . ($moderator->canModerateContent() ? "YES" : "NO") . "\n";
    echo "Can suspend user: " . ($moderator->canSuspendUser() ? "YES" : "NO") . "\n";
    
    $admin = UserFactory::createAdministrator("admin_user", "admin@test.com", "password123", true);
    echo "\nAdministrator created: " . $admin->getUsername() . "\n";
    echo "Has full access: " . ($admin->hasFullAccess() ? "YES" : "NO") . "\n";
    echo "Is super: " . ($admin->isSuper() ? "YES" : "NO") . "\n";
    
    $userData = [
        'id' => 100,
        'username' => 'test_from_array',
        'email' => 'array@test.com',
        'password' => 'hashed',
        'role' => 'ProUser',
        'subscriptionEnd' => '2026-12-31 23:59:59'
    ];
    $userFromArray = UserFactory::createFromArray($userData);
    echo "\nUser from array: " . $userFromArray->getUsername() . " (" . $userFromArray->getRole() . ")\n\n";
}

function testUserModel() {
    echo "=== Testing User Models ===\n";
    
    $user = UserFactory::createBasicUser("test_user", "test@example.com", "mypassword");
    
    echo "Username: " . $user->getUsername() . "\n";
    echo "Email: " . $user->getEmail() . "\n";
    echo "Role: " . $user->getRole() . "\n";
    
    $user->setUsername("updated_user");
    $user->setEmail("updated@example.com");
    $user->setBio("This is my bio");
    $user->setProfilePicture("profile.jpg");
    echo "Updated username: " . $user->getUsername() . "\n";
    echo "Updated email: " . $user->getEmail() . "\n";
    echo "Bio: " . $user->getBio() . "\n";
    
    echo "Password verification: " . ($user->verifyPassword("mypassword") ? "SUCCESS" : "FAILED") . "\n";
    echo "Wrong password: " . ($user->verifyPassword("wrongpass") ? "FAILED" : "SUCCESS") . "\n";
    
    $user->incrementUploadCount();
    $user->incrementUploadCount();
    echo "Upload count: " . $user->getUploadCount() . "\n";
    
    $userArray = $user->toArray();
    echo "User as array has keys: " . implode(", ", array_keys($userArray)) . "\n\n";
}

function testPhotoModel() {
    echo "=== Testing Photo Model ===\n";
    
    $photoData = [
        'title' => 'Sunset Beach',
        'description' => 'Beautiful sunset at the beach',
        'imageLink' => 'uploads/sunset.jpg',
        'fileSize' => 2048000,
        'dimensions' => '1920x1080',
        'state' => 'draft',
        'userId' => 1,
        'isPublic' => true
    ];
    
    $photo = new Photo($photoData);
    
    echo "Photo title: " . $photo->getTitle() . "\n";
    echo "Description: " . $photo->getDescription() . "\n";
    echo "Image link: " . $photo->getImageLink() . "\n";
    echo "File size: " . $photo->getFileSize() . " bytes\n";
    echo "Dimensions: " . $photo->getDimensions() . "\n";
    echo "State: " . $photo->getState() . "\n";
    echo "Is draft: " . ($photo->isDraft() ? "YES" : "NO") . "\n";
    echo "Is published: " . ($photo->isPublished() ? "YES" : "NO") . "\n";
    echo "Is public: " . ($photo->isPublic() ? "YES" : "NO") . "\n";
    
    $photo->setTitle("Updated Sunset");
    $photo->setDescription("Even more beautiful");
    echo "Updated title: " . $photo->getTitle() . "\n";
    
    $photo->publish();
    echo "After publish - State: " . $photo->getState() . "\n";
    echo "Is published: " . ($photo->isPublished() ? "YES" : "NO") . "\n";
    
    $photo->incrementViewCount();
    $photo->incrementViewCount();
    $photo->incrementViewCount();
    echo "View count: " . $photo->getViewCount() . "\n";
    
    $photo->addTag("nature");
    $photo->addTag("sunset");
    $photo->addTag("beach");
    echo "Tags: " . implode(", ", $photo->getTags()) . "\n";
    echo "Has tag 'sunset': " . ($photo->hasTag("sunset") ? "YES" : "NO") . "\n";
    echo "Has tag 'mountain': " . ($photo->hasTag("mountain") ? "YES" : "NO") . "\n";
    
    $photo->removeTag("beach");
    echo "Tags after removal: " . implode(", ", $photo->getTags()) . "\n";
    
    echo "Has all tags [nature, sunset]: " . ($photo->hasAllTags(['nature', 'sunset']) ? "YES" : "NO") . "\n";
    echo "Has any tag [beach, sunset]: " . ($photo->hasAnyTag(['beach', 'sunset']) ? "YES" : "NO") . "\n";
    
    $photo->addLike(1);
    $photo->addLike(2);
    $photo->addLike(3);
    echo "Like count: " . $photo->getLikeCount() . "\n";
    echo "Is liked by user 2: " . ($photo->isLikedBy(2) ? "YES" : "NO") . "\n";
    echo "Is liked by user 5: " . ($photo->isLikedBy(5) ? "YES" : "NO") . "\n";
    
    $photo->removeLike(2);
    echo "Like count after removal: " . $photo->getLikeCount() . "\n";
    
    $commentId1 = $photo->addComment("Great photo!", 1);
    $commentId2 = $photo->addComment("Amazing colors!", 2);
    echo "Comment count: " . $photo->getCommentCount() . "\n";
    echo "Comments: " . count($photo->getComments()) . " items\n";
    
    $photo->removeComment($commentId1);
    echo "Comment count after removal: " . $photo->getCommentCount() . "\n";
    
    $photo->archive();
    echo "State after archive: " . $photo->getState() . "\n";
    
    $photo->setPublic(false);
    echo "Is public after setting to private: " . ($photo->isPublic() ? "YES" : "NO") . "\n";
    
    $photo->clearTags();
    echo "Tags after clear: " . count($photo->getTags()) . "\n\n";
}

function testAlbumModel() {
    echo "=== Testing Album Model ===\n";
    
    $albumData = [
        'id' => 1,
        'name' => 'Vacation 2025',
        'public' => true,
        'cover' => 'cover.jpg',
        'photoCount' => 15,
        'publisherId' => 1
    ];
    
    $album = new Album($albumData);
    
    echo "Album name: " . $album->getName() . "\n";
    echo "Is public: " . ($album->isPublic() ? "YES" : "NO") . "\n";
    echo "Cover: " . $album->getCover() . "\n";
    echo "Photo count: " . $album->getPhotoCount() . "\n";
    echo "Publisher ID: " . $album->getPublisherId() . "\n";
    
    $album->setName("Summer Vacation 2025");
    $album->setPublic(false);
    $album->setCover("new_cover.jpg");
    echo "Updated name: " . $album->getName() . "\n";
    echo "Is public after update: " . ($album->isPublic() ? "YES" : "NO") . "\n";
    
    $album->incrementPhotoCount();
    $album->incrementPhotoCount();
    echo "Photo count after increments: " . $album->getPhotoCount() . "\n";
    
    $album->decrementPhotoCount();
    echo "Photo count after decrement: " . $album->getPhotoCount() . "\n";
    
    $albumArray = $album->toArray();
    echo "Album as array has " . count($albumArray) . " keys\n\n";
}

function testTagModel() {
    echo "=== Testing Tag Model ===\n";
    
    $tagData = [
        'id' => 1,
        'slug' => 'nature-photography',
        'photoCount' => 250
    ];
    
    $tag = new Tag($tagData);
    
    echo "Tag slug: " . $tag->getSlug() . "\n";
    echo "Photo count: " . $tag->getPhotoCount() . "\n";
    
    $tag->incrementPhotoCount();
    echo "Photo count after increment: " . $tag->getPhotoCount() . "\n";
    
    $tag->decrementPhotoCount();
    echo "Photo count after decrement: " . $tag->getPhotoCount() . "\n";
    
    $normalized1 = Tag::normalizeSlug("Nature Photography!");
    $normalized2 = Tag::normalizeSlug("  Sunset-Beach  ");
    $normalized3 = Tag::normalizeSlug("Urban___Life");
    echo "Normalized 'Nature Photography!': " . $normalized1 . "\n";
    echo "Normalized '  Sunset-Beach  ': " . $normalized2 . "\n";
    echo "Normalized 'Urban___Life': " . $normalized3 . "\n";
    
    $tagArray = $tag->toArray();
    echo "Tag as array has " . count($tagArray) . " keys\n\n";
}

function testCommentModel() {
    echo "=== Testing Comment Model ===\n";
    
    $commentData = [
        'id' => 1,
        'content' => 'This is a great photo!',
        'isArchive' => false,
        'userId' => 5,
        'photoId' => 10
    ];
    
    $comment = new Comment($commentData);
    
    echo "Comment ID: " . $comment->getId() . "\n";
    echo "Content: " . $comment->getContent() . "\n";
    echo "User ID: " . $comment->getUserId() . "\n";
    echo "Photo ID: " . $comment->getPhotoId() . "\n";
    echo "Is archived: " . ($comment->isArchived() ? "YES" : "NO") . "\n";
    
    $comment->setContent("Updated comment text");
    echo "Updated content: " . $comment->getContent() . "\n";
    
    $comment->archive();
    echo "Is archived after archive(): " . ($comment->isArchived() ? "YES" : "NO") . "\n";
    
    $commentArray = $comment->toArray();
    echo "Comment as array has " . count($commentArray) . " keys\n\n";
}

function testLikeModel() {
    echo "=== Testing Like Model ===\n";
    
    $likeData = [
        'userId' => 7,
        'photoId' => 12
    ];
    
    $like = new Like($likeData);
    
    echo "User ID: " . $like->getUserId() . "\n";
    echo "Photo ID: " . $like->getPhotoId() . "\n";
    
    $likeArray = $like->toArray();
    echo "Like as array has " . count($likeArray) . " keys\n\n";
}

function testUserRepository() {
    echo "=== Testing UserRepository ===\n";
    
    try {
        $repo = new UserRepository();
        
        $userData = [
            'username' => 'repo_test_user',
            'email' => 'repo_test@example.com',
            'password' => 'testpass123',
            'role' => 'BasicUser',
            'bio' => 'Test bio'
        ];
        
        echo "Creating user...\n";
        $user = $repo->create($userData);
        echo "User created with ID: " . $user->getId() . "\n";
        
        echo "Finding by ID...\n";
        $foundUser = $repo->findById($user->getId());
        echo "Found user: " . ($foundUser ? $foundUser->getUsername() : "NOT FOUND") . "\n";
        
        echo "Finding by email...\n";
        $foundByEmail = $repo->findByEmail('repo_test@example.com');
        echo "Found by email: " . ($foundByEmail ? $foundByEmail->getUsername() : "NOT FOUND") . "\n";
        
        echo "Finding by username...\n";
        $foundByUsername = $repo->findByUsername('repo_test_user');
        echo "Found by username: " . ($foundByUsername ? $foundByUsername->getEmail() : "NOT FOUND") . "\n";
        
        echo "Authenticating...\n";
        $authUser = $repo->authenticate('repo_test@example.com', 'testpass123');
        echo "Authentication: " . ($authUser ? "SUCCESS" : "FAILED") . "\n";
        
        echo "Authenticating with wrong password...\n";
        $authFail = $repo->authenticate('repo_test@example.com', 'wrongpass');
        echo "Wrong password auth: " . ($authFail ? "FAILED" : "SUCCESS (correctly rejected)") . "\n";
        
        echo "Updating user...\n";
        $user->setBio("Updated bio from repository test");
        $updateResult = $repo->update($user);
        echo "Update: " . ($updateResult ? "SUCCESS" : "FAILED") . "\n";
        
        echo "Finding all users...\n";
        $allUsers = $repo->findAll(10, 0);
        echo "Found " . count($allUsers) . " users\n";
        
        echo "Deleting user...\n";
        $deleteResult = $repo->delete($user->getId());
        echo "Delete: " . ($deleteResult ? "SUCCESS" : "FAILED") . "\n\n";
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
}

function testAlbumRepository() {
    echo "=== Testing AlbumRepository ===\n";
    
    try {
        $repo = new AlbumRepository();
        
        echo "Album exists check (non-existent): " . ($repo->albumExists(99999) ? "EXISTS" : "NOT FOUND") . "\n";
        
        echo "\nNote: Full AlbumRepository testing requires valid user and photo IDs in database\n";
        echo "Methods available:\n";
        echo "- createAlbum(userId, title, cover, isPrivate)\n";
        echo "- addPhotoToAlbum(albumId, photoId, userId)\n";
        echo "- removePhotoFromAlbum(albumId, photoId, userId)\n";
        echo "- getAlbumWithPhotos(albumId, userId)\n";
        echo "- getUserAlbums(userId, includePrivate)\n";
        echo "- updateAlbum(albumId, userId, data)\n";
        echo "- deleteAlbum(albumId, userId)\n";
        echo "- albumExists(albumId)\n";
        echo "- getAlbumStats(albumId)\n\n";
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
}

function testTagRepository() {
    echo "=== Testing TagRepository ===\n";
    
    try {
        $repo = new TagRepository();
        
        echo "Getting popular tags...\n";
        $popularTags = $repo->getPopularTags(10);
        echo "Found " . count($popularTags) . " popular tags\n";
        
        echo "\nSearching tags with 'photo'...\n";
        $searchResults = $repo->searchTags('photo', 5);
        echo "Found " . count($searchResults) . " matching tags\n";
        
        echo "\nNote: Full TagRepository testing requires data in database\n";
        echo "Methods available:\n";
        echo "- getPopularTags(limit)\n";
        echo "- searchTags(query, limit)\n";
        echo "- getPhotosByTag(tagName, page, perPage)\n";
        echo "- getTagStats(tagName)\n";
        echo "- mergeTags(fromTag, toTag)\n\n";
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
}

function testTimestampableTrait() {
    echo "=== Testing TimestampableTrait (via Photo) ===\n";
    
    $photo = new Photo([
        'title' => 'Test Photo',
        'imageLink' => 'test.jpg',
        'userId' => 1
    ]);
    
    echo "Created at: " . $photo->getCreatedAt('Y-m-d H:i:s') . "\n";
    echo "Updated at: " . $photo->getUpdatedAt('Y-m-d H:i:s') . "\n";
    
    sleep(1);
    $photo->setTitle("Modified Title");
    echo "Updated at after modification: " . $photo->getUpdatedAt('Y-m-d H:i:s') . "\n\n";
}

function runAllTests() {
    echo "╔════════════════════════════════════════╗\n";
    echo "║   PhotoSphere Project Test Suite      ║\n";
    echo "╚════════════════════════════════════════╝\n\n";
    
    testDatabase();
    testUserFactory();
    testUserModel();
    testPhotoModel();
    testAlbumModel();
    testTagModel();
    testCommentModel();
    testLikeModel();
    testTimestampableTrait();
    testUserRepository();
    testAlbumRepository();
    testTagRepository();
    
    echo "╔════════════════════════════════════════╗\n";
    echo "║     All Tests Completed!               ║\n";
    echo "╚════════════════════════════════════════╝\n";
}

runAllTests();

?>