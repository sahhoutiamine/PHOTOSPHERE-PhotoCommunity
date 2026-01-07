<?php

require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Album.php';
require_once __DIR__ . '/../Models/Photo.php';


class AlbumRepository {
    
    private const MAX_PHOTOS_PER_ALBUM = 100;
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function createAlbum(int $userId, string $title, string $cover, bool $isPrivate): int {
        if ($isPrivate) {
            $user = $this->getUserWithPermissions($userId);
            if (!$this->userCanCreatePrivateAlbum($user)) {
                throw new Exception("Seuls les utilisateurs Pro et supérieurs peuvent créer des albums privés");
            }
        }
        
        $stmt = $this->db->prepare("SELECT id FROM albums WHERE name = ? AND publisherId = ?");
        $stmt->execute([$title, $userId]);
        if ($stmt->fetch()) {
            throw new Exception("Un album avec ce titre existe déjà pour cet utilisateur");
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO albums (name, public, cover, createdAt, updatedAt, publisherId)
            VALUES (?, ?, ?, NOW(), NOW(), ?)
        ");
        
        $public = !$isPrivate;
        $stmt->execute([$title, $public, $cover, $userId]);
        
        return (int)$this->db->lastInsertId();
    }
    public function addPhotoToAlbum(int $albumId, int $photoId, int $userId): bool {

    }
    public function removePhotoFromAlbum(int $albumId, int $photoId, int $userId): bool {

    }
    public function getAlbumWithPhotos(int $albumId, int $userId): ?array {

    }
    public function getUserAlbums(int $userId, bool $includePrivate = true): array {

    }
    public function updateAlbum(int $albumId, int $userId, array $data): bool {

    }
    public function deleteAlbum(int $albumId, int $userId): bool {

    }
}
