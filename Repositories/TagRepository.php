<?php
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../Models/Tag.php';


class TagRepository {
    
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getPopularTags(int $limit = 50): array {
        $sql = "SELECT * FROM tags ORDER BY photoCount DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $tags = [];
        while ($data = $stmt->fetch()) {
            $tags[] = new Tag($data);
        }
        
        return $tags;
    }
    public function searchTags(string $query, int $limit = 20): array {
        $sql = "SELECT * FROM tags WHERE slug LIKE :query ORDER BY photoCount DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':query', '%' . strtolower($query) . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $tags = [];
        while ($data = $stmt->fetch()) {
            $tags[] = new Tag($data);
        }
        
        return $tags;
    }
    public function getPhotosByTag(string $tagName, int $page = 1, int $perPage = 30): array {
        $offset = ($page - 1) * $perPage;
        $normalizedTag = Tag::normalizeSlug($tagName);
        
        $sql = "SELECT p.* FROM photos p
                INNER JOIN photo_tags pt ON p.id = pt.photoId
                INNER JOIN tags t ON pt.tagId = t.id
                WHERE t.slug = :slug AND p.state = 'published'
                ORDER BY p.publishedAt DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':slug', $normalizedTag, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $photos = [];
        while ($data = $stmt->fetch()) {
            $photos[] = new Photo($data);
        }
        
        return $photos;
    }
    
    public function getTagStats(string $tagName): array {
        $normalizedTag = Tag::normalizeSlug($tagName);
        
        $sql = "SELECT t.*, 
                COUNT(DISTINCT pt.photoId) as totalPhotos,
                COUNT(DISTINCT p.userId) as totalUsers
                FROM tags t
                LEFT JOIN photo_tags pt ON t.id = pt.tagId
                LEFT JOIN photos p ON pt.photoId = p.id
                WHERE t.slug = :slug
                GROUP BY t.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':slug', $normalizedTag, PDO::PARAM_STR);
        $stmt->execute();
        
        $data = $stmt->fetch();
        
        if (!$data) {
            return [];
        }
        
        return [
            'tag' => new Tag($data),
            'totalPhotos' => (int)$data['totalPhotos'],
            'totalUsers' => (int)$data['totalUsers']
        ];
    }
    public function mergeTags(string $fromTag, string $toTag): bool {

    }

}
