<?php
require_once __DIR__ . '/../Interfaces/Repositories.php';

class TagRepository implements TagRepositoryInterface {
    private PDO $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function create(string $slug): Tag {
        $stmt = $this->db->prepare("INSERT INTO tags (slug) VALUES (?)");
        $stmt->execute([$slug]);
        
        return new Tag([
            'id' => (int)$this->db->lastInsertId(),
            'slug' => $slug,
            'photoCount' => 0
        ]);
    }
    
    public function findById(int $id): ?Tag {
        $stmt = $this->db->prepare("SELECT * FROM tags WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        return $data ? new Tag($data) : null;
    }
    
    public function findBySlug(string $slug): ?Tag {
        $stmt = $this->db->prepare("SELECT * FROM tags WHERE slug = ?");
        $stmt->execute([$slug]);
        $data = $stmt->fetch();
        
        return $data ? new Tag($data) : null;
    }
    
    public function findOrCreate(string $slug): Tag {
        $normalizedSlug = Tag::normalizeSlug($slug);
        $tag = $this->findBySlug($normalizedSlug);
        
        if (!$tag) {
            $tag = $this->create($normalizedSlug);
        }
        
        return $tag;
    }
    
    public function update(Tag $tag): bool {
        $stmt = $this->db->prepare("UPDATE tags SET photoCount = :photoCount WHERE id = :id");
        return $stmt->execute([
            'photoCount' => $tag->getPhotoCount(),
            'id' => $tag->getId()
        ]);
    }
    
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM tags WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function findAll(): array {
        $stmt = $this->db->query("SELECT * FROM tags ORDER BY slug ASC");
        
        $tags = [];
        while ($data = $stmt->fetch()) {
            $tags[] = new Tag($data);
        }
        
        return $tags;
    }
    
    public function findPopular(int $limit = 20): array {
        $stmt = $this->db->prepare("SELECT * FROM tags WHERE photoCount > 0 ORDER BY photoCount DESC LIMIT ?");
        $stmt->execute([$limit]);
        
        $tags = [];
        while ($data = $stmt->fetch()) {
            $tags[] = new Tag($data);
        }
        
        return $tags;
    }
}