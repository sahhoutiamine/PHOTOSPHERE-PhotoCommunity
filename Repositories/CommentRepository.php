<?php
require_once __DIR__ . '/../Interfaces/Repositories.php';

class CommentRepository implements CommentRepositoryInterface {
    private PDO $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function create(array $data): Comment {
        $sql = "INSERT INTO comments (content, userId, photoId) VALUES (:content, :userId, :photoId)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'content' => $data['content'],
            'userId' => $data['userId'],
            'photoId' => $data['photoId']
        ]);
        
        $data['id'] = (int)$this->db->lastInsertId();
        return new Comment($data);
    }
    
    public function findById(int $id): ?Comment {
        $stmt = $this->db->prepare("SELECT * FROM comments WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        return $data ? new Comment($data) : null;
    }
    
    public function update(Comment $comment): bool {
        $stmt = $this->db->prepare("UPDATE comments SET content = :content WHERE id = :id");
        return $stmt->execute([
            'content' => $comment->getContent(),
            'id' => $comment->getId()
        ]);
    }
    
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("UPDATE comments SET isArchive = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function findByPhotoId(int $photoId): array {
        $stmt = $this->db->prepare("SELECT * FROM comments WHERE photoId = ? AND isArchive = 0 ORDER BY createdAt DESC");
        $stmt->execute([$photoId]);
        
        $comments = [];
        while ($data = $stmt->fetch()) {
            $comments[] = new Comment($data);
        }
        
        return $comments;
    }
    
    public function findByUserId(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM comments WHERE userId = ? AND isArchive = 0 ORDER BY createdAt DESC");
        $stmt->execute([$userId]);
        
        $comments = [];
        while ($data = $stmt->fetch()) {
            $comments[] = new Comment($data);
        }
        
        return $comments;
    }
}