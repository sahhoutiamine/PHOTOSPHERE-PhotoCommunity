<?php

class AlbumRepository {
    
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function createAlbum(int $userId, string $title, string $description, bool $isPrivate): int {

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
