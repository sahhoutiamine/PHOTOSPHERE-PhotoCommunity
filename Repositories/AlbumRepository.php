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
        $stmt = $this->db->prepare("SELECT id FROM albums WHERE id = ? AND publisherId = ?");
        $stmt->execute([$albumId, $userId]);
        if (!$stmt->fetch()) {
            throw new Exception("Album non trouvé ou vous n'en êtes pas le propriétaire");
        }
        
        $stmt = $this->db->prepare("SELECT id FROM photos WHERE id = ? AND userId = ?");
        $stmt->execute([$photoId, $userId]);
        if (!$stmt->fetch()) {
            throw new Exception("Photo non trouvée ou vous n'en êtes pas le propriétaire");
        }
        
        $stmt = $this->db->prepare("SELECT photoId FROM photos WHERE id = ? AND albumId = ?");
        $stmt->execute([$photoId, $albumId]);
        if ($stmt->fetch()) {
            throw new Exception("Cette photo est déjà dans l'album");
        }
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM photos WHERE albumId = ?");
        $stmt->execute([$albumId]);
        $result = $stmt->fetch();
        if ($result['count'] >= self::MAX_PHOTOS_PER_ALBUM) {
            throw new Exception("L'album ne peut contenir plus de " . self::MAX_PHOTOS_PER_ALBUM . " photos");
        }
        
        $stmt = $this->db->prepare("UPDATE photos SET albumId = ? WHERE id = ?");
        $success = $stmt->execute([$albumId, $photoId]);
        
        if ($success) {
            $this->updateAlbumPhotoCount($albumId);
            
            $stmt = $this->db->prepare("UPDATE albums SET updatedAt = NOW() WHERE id = ?");
            $stmt->execute([$albumId]);
        }
        
        return $success;
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
