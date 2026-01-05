<?php
require_once __DIR__ . '/../Interfaces/Repositories.php';

class LikeRepository implements LikeRepositoryInterface {
    private PDO $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function create(int $userId, int $photoId): bool {
        try {
            $stmt = $this->db->prepare("INSERT INTO likes (userId, photoId) VALUES (?, ?)");
            return $stmt->execute([$userId, $photoId]);
        } catch (PDOException $e) {
            // Handle duplicate key constraint
            return false;
        }
    }
    
    public function delete(int $userId, int $photoId): bool {
        $stmt = $this->db->prepare("DELETE FROM likes WHERE userId = ? AND photoId = ?");
        return $stmt->execute([$userId, $photoId]);
    }
    
    public function exists(int $userId, int $photoId): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE userId = ? AND photoId = ?");
        $stmt->execute([$userId, $photoId]);
        return $stmt->fetchColumn() > 0;
    }
    
    public function findByPhotoId(int $photoId): array {
        $stmt = $this->db->prepare("SELECT * FROM likes WHERE photoId = ? ORDER BY createdAt DESC");
        $stmt->execute([$photoId]);
        
        $likes = [];
        while ($data = $stmt->fetch()) {
            $likes[] = new Like($data);
        }
        
        return $likes;
    }
    
    public function findByUserId(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM likes WHERE userId = ? ORDER BY createdAt DESC");
        $stmt->execute([$userId]);
        
        $likes = [];
        while ($data = $stmt->fetch()) {
            $likes[] = new Like($data);
        }
        
        return $likes;
    }
    
    public function countByPhotoId(int $photoId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE photoId = ?");
        $stmt->execute([$photoId]);
        return (int)$stmt->fetchColumn();
    }
}