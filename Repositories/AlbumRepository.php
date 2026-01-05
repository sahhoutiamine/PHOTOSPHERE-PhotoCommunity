<?php
require_once __DIR__ . '/../Interfaces/Repositories.php';

class AlbumRepository implements AlbumRepositoryInterface {
    private PDO $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function create(array $data): Album {
        $sql = "INSERT INTO albums (name, public, cover, publisherId) 
                VALUES (:name, :public, :cover, :publisherId)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'public' => $data['public'] ?? 1,
            'cover' => $data['cover'] ?? null,
            'publisherId' => $data['publisherId']
        ]);
        
        $data['id'] = (int)$this->db->lastInsertId();
        return new Album($data);
    }
    
    public function findById(int $id): ?Album {
        $stmt = $this->db->prepare("SELECT * FROM albums WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        return $data ? new Album($data) : null;
    }
    
    public function update(Album $album): bool {
        $sql = "UPDATE albums SET 
                name = :name, 
                public = :public, 
                cover = :cover,
                photoCount = :photoCount
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'name' => $album->getName(),
            'public' => $album->isPublic(),
            'cover' => $album->getCover(),
            'photoCount' => $album->getPhotoCount(),
            'id' => $album->getId()
        ]);
    }
    
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM albums WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function findByPublisherId(int $publisherId): array {
        $stmt = $this->db->prepare("SELECT * FROM albums WHERE publisherId = ? ORDER BY updatedAt DESC");
        $stmt->execute([$publisherId]);
        
        $albums = [];
        while ($data = $stmt->fetch()) {
            $albums[] = new Album($data);
        }
        
        return $albums;
    }
    
    public function findPublic(int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare("SELECT * FROM albums WHERE public = 1 ORDER BY updatedAt DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        
        $albums = [];
        while ($data = $stmt->fetch()) {
            $albums[] = new Album($data);
        }
        
        return $albums;
    }
    
    public function updatePhotoCount(int $albumId): bool {
        $stmt = $this->db->prepare("UPDATE albums SET photoCount = (SELECT COUNT(*) FROM photos WHERE albumId = ?) WHERE id = ?");
        return $stmt->execute([$albumId, $albumId]);
    }
}