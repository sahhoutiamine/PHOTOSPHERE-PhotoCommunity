<?php
require_once __DIR__ . '/../Interfaces/Repositories.php';

class PhotoRepository implements PhotoRepositoryInterface {
    private PDO $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function create(array $data): Photo {
        $sql = "INSERT INTO photos (title, description, imageLink, fileSize, dimensions, state, userId, albumId) 
                VALUES (:title, :description, :imageLink, :fileSize, :dimensions, :state, :userId, :albumId)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'imageLink' => $data['imageLink'],
            'fileSize' => $data['fileSize'] ?? null,
            'dimensions' => $data['dimensions'] ?? null,
            'state' => $data['state'] ?? 'draft',
            'userId' => $data['userId'],
            'albumId' => $data['albumId'] ?? null
        ]);
        
        $data['id'] = (int)$this->db->lastInsertId();
        return new Photo($data);
    }
    
    public function findById(int $id): ?Photo {
        $stmt = $this->db->prepare("SELECT * FROM photos WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        return $data ? new Photo($data) : null;
    }
    
    public function update(Photo $photo): bool {
        $sql = "UPDATE photos SET 
                title = :title, 
                description = :description, 
                state = :state, 
                albumId = :albumId,
                publishedAt = :publishedAt
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'title' => $photo->getTitle(),
            'description' => $photo->getDescription(),
            'state' => $photo->getState(),
            'albumId' => $photo->getAlbumId(),
            'publishedAt' => $photo->getPublishedAt(),
            'id' => $photo->getId()
        ]);
    }
    
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM photos WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function findByUserId(int $userId, int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare("SELECT * FROM photos WHERE userId = ? ORDER BY createdAt DESC LIMIT ? OFFSET ?");
        $stmt->execute([$userId, $limit, $offset]);
        
        $photos = [];
        while ($data = $stmt->fetch()) {
            $photos[] = new Photo($data);
        }
        
        return $photos;
    }
    
    public function findByAlbumId(int $albumId): array {
        $stmt = $this->db->prepare("SELECT * FROM photos WHERE albumId = ? ORDER BY createdAt DESC");
        $stmt->execute([$albumId]);
        
        $photos = [];
        while ($data = $stmt->fetch()) {
            $photos[] = new Photo($data);
        }
        
        return $photos;
    }
    
    public function findPublished(int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare("SELECT * FROM photos WHERE state = 'published' ORDER BY publishedAt DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        
        $photos = [];
        while ($data = $stmt->fetch()) {
            $photos[] = new Photo($data);
        }
        
        return $photos;
    }
    
    public function incrementViewCount(int $photoId): bool {
        $stmt = $this->db->prepare("UPDATE photos SET viewCount = viewCount + 1 WHERE id = ?");
        return $stmt->execute([$photoId]);
    }
    
    public function findByState(string $state, int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare("SELECT * FROM photos WHERE state = ? ORDER BY createdAt DESC LIMIT ? OFFSET ?");
        $stmt->execute([$state, $limit, $offset]);
        
        $photos = [];
        while ($data = $stmt->fetch()) {
            $photos[] = new Photo($data);
        }
        
        return $photos;
    }
}