<?php
require_once __DIR__ . '/../Interfaces/Repositories.php';

class PhotoTagRepository implements PhotoTagRepositoryInterface {
    private PDO $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function addTag(int $photoId, int $tagId): bool {
        try {
            $stmt = $this->db->prepare("INSERT INTO photo_tags (photoId, tagId) VALUES (?, ?)");
            $result = $stmt->execute([$photoId, $tagId]);
            
            if ($result) {
                // Increment tag photo count
                $stmt = $this->db->prepare("UPDATE tags SET photoCount = photoCount + 1 WHERE id = ?");
                $stmt->execute([$tagId]);
            }
            
            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function removeTag(int $photoId, int $tagId): bool {
        $stmt = $this->db->prepare("DELETE FROM photo_tags WHERE photoId = ? AND tagId = ?");
        $result = $stmt->execute([$photoId, $tagId]);
        
        if ($result && $stmt->rowCount() > 0) {
            // Decrement tag photo count
            $stmt = $this->db->prepare("UPDATE tags SET photoCount = GREATEST(0, photoCount - 1) WHERE id = ?");
            $stmt->execute([$tagId]);
        }
        
        return $result;
    }
    
    public function findTagsByPhotoId(int $photoId): array {
        $stmt = $this->db->prepare("
            SELECT t.* FROM tags t
            INNER JOIN photo_tags pt ON t.id = pt.tagId
            WHERE pt.photoId = ?
        ");
        $stmt->execute([$photoId]);
        
        $tags = [];
        while ($data = $stmt->fetch()) {
            $tags[] = new Tag($data);
        }
        
        return $tags;
    }
    
    public function findPhotosByTagId(int $tagId): array {
        $stmt = $this->db->prepare("
            SELECT p.* FROM photos p
            INNER JOIN photo_tags pt ON p.id = pt.photoId
            WHERE pt.tagId = ?
        ");
        $stmt->execute([$tagId]);
        
        $photos = [];
        while ($data = $stmt->fetch()) {
            $photos[] = new Photo($data);
        }
        
        return $photos;
    }
}