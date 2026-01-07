<?php
require_once __DIR__ . '/../Database.php';

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
        
    }
    public function getPhotosByTag(string $tagName, int $page = 1, int $perPage = 30): array {

    }
    public function  getTagStats(string $tagName): array {

    }
    public function mergeTags(string $fromTag, string $toTag): bool {

    }

}


?>