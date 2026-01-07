# PHOTOSPHERE - PhotoCommunity

PHOTOSPHERE is a robust PHP backend system designed for a photo sharing community. It implements a clean architecture using the Repository pattern to manage Users, Albums, Photos, and Tags efficiently.

## 📂 Project Structure

The project is organized into the following key directories:

- **`Models`**: Contains the entity classes representing the business objects.
  - `User` (Abstract parent) and subclasses:
    - `BasicUser`: Standard user with basic permissions.
    - `ProUser`: Paid user with premium features (e.g., private albums).
    - `Administrator` & `Moderator`: Staff roles with elevated privileges.
  - `Album`: Collections of photos (supports public/private visibility).
  - `Photo`: The core content entity.
  - `Tag`: For categorization and discovery.
  - `Comment`, `Like`: Interaction models.

- **`Repositories`**: Handles data persistence and business logic for database interactions.
  - `UserRepository`: User CRUD, authentication, and role management.
  - `AlbumRepository`: Album creation, photo management within albums, and statistics.
  - `TagRepository`: Tag search, advanced filtering, and tag merging capabilities.

- **`Services`**:
  - `UserFactory`: A factory class to instantiate the correct User subclass based on role/data.

- **`Database.php`**: A Singleton database wrapper ensuring a single shared PDO connection.

## ⚙️ Setup & Configuration

1. **Database**
   - Import the schema (not included in this repo, ensure your `photospheredb` exists).
   - The system expects a MySQL database named `photospheredb`.
   
2. **Connection**
   - Check `Database.php` to configure your database credentials if different from defaults:
     ```php
     private $host = 'localhost';
     private $dbname = 'photospheredb';
     private $username = 'root';
     private $password = '';
     ```

## 🚀 Usage

### User Management
The `UserRepository` allows you to create, find, update, and authenticate users.

```php
require_once __DIR__ . '/Repositories/UserRepository.php';

$userRepo = new UserRepository();

// Create a new Basic User
$user = $userRepo->create([
    'username' => 'photographer_one',
    'email' => 'photo@example.com',
    'password' => 'securepass',
    'role' => 'BasicUser'
]);

// Authenticate
$loggedInUser = $userRepo->authenticate('photo@example.com', 'securepass');

if ($loggedInUser) {
    echo "Welcome back, " . $loggedInUser->getUsername();
}
```

### Album Management
Manage albums and their privacy settings using `AlbumRepository`.

```php
require_once __DIR__ . '/Repositories/AlbumRepository.php';

$albumRepo = new AlbumRepository();

// Create a public album (last valid arg is isPrivate)
$albumId = $albumRepo->createAlbum($user->getId(), "Summer Vacation", "cover.jpg", false);

// Add a photo to the album
$albumRepo->addPhotoToAlbum($albumId, $photoId, $user->getId());

// Create a private album (Pro users only)
try {
    $privateAlbumId = $albumRepo->createAlbum($proUserId, "Exclusive Shoots", "secret.jpg", true);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Tagging System
The `TagRepository` offers advanced features like tag searching and merging.

```php
require_once __DIR__ . '/Repositories/TagRepository.php';

$tagRepo = new TagRepository();

// Search for tags
$tags = $tagRepo->searchTags('nature');
foreach ($tags as $tag) {
    echo $tag->getSlug();
}

// Merge duplicate tags (e.g., merging 'sunset-pics' into 'sunset')
$tagRepo->mergeTags('sunset-pics', 'sunset');
```

## 🧪 Testing

The project includes a comprehensive test suite in `test.php`. This script sets up the environment, runs a series of functional tests, and asserts expected behaviors.

To run the tests:

```bash
php test.php
```

The test runner will output the status of each test case (PASS/FAIL) with colored output for better visibility.
