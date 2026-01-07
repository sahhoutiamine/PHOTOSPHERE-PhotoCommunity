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
        public function removePhotoFromAlbum(int $albumId, int $photoId, int $userId): bool {
        $stmt = $this->db->prepare("SELECT id FROM albums WHERE id = ? AND publisherId = ?");
        $stmt->execute([$albumId, $userId]);
        if (!$stmt->fetch()) {
            throw new Exception("Album non trouvé ou vous n'en êtes pas le propriétaire");
        }
        
        $stmt = $this->db->prepare("SELECT id FROM photos WHERE id = ? AND albumId = ?");
        $stmt->execute([$photoId, $albumId]);
        if (!$stmt->fetch()) {
            throw new Exception("Cette photo n'est pas dans l'album");
        }
        
        $stmt = $this->db->prepare("UPDATE photos SET albumId = NULL WHERE id = ?");
        $success = $stmt->execute([$photoId]);
        
        if ($success) {
            $this->updateAlbumPhotoCount($albumId);
            
            $stmt = $this->db->prepare("UPDATE albums SET updatedAt = NOW() WHERE id = ?");
            $stmt->execute([$albumId]);
        }
        
        return $success;
    }
    }
    public function getAlbumWithPhotos(int $albumId, int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT a.*, u.username as publisherUsername, u.profilePicture as publisherProfilePicture
            FROM albums a
            JOIN users u ON a.publisherId = u.id
            WHERE a.id = ?
        ");
        $stmt->execute([$albumId]);
        $album = $stmt->fetch();
        
        if (!$album) {
            return null;
        }
        
        if (!$album['public'] && $album['publisherId'] != $userId) {
            throw new Exception("Cet album est privé et vous n'êtes pas autorisé à le consulter");
        }
        
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        $offset = ($page - 1) * $limit;
        
        $stmt = $this->db->prepare("
            SELECT p.*, u.username as photoOwnerUsername
            FROM photos p
            JOIN users u ON p.userId = u.id
            WHERE p.albumId = ? AND p.state = 'published'
            ORDER BY p.createdAt DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$albumId, $limit, $offset]);
        $photos = $stmt->fetchAll();
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM photos WHERE albumId = ? AND state = 'published'");
        $stmt->execute([$albumId]);
        $totalResult = $stmt->fetch();
        $totalPhotos = $totalResult['total'];
        
        return [
            'album' => $album,
            'photos' => $photos,
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => (int)$limit,
                'total_items' => (int)$totalPhotos,
                'total_pages' => ceil($totalPhotos / $limit)
            ]
        ];
    }
    
    public function getUserAlbums(int $userId, bool $includePrivate = true): array {
        $page = $_GET['page'] ?? 1;
        $limit = $_GET['limit'] ?? 20;
        $offset = ($page - 1) * $limit;
        
        $query = "
            SELECT a.*, 
                   u.username as publisherUsername,
                   (SELECT COUNT(*) FROM photos p WHERE p.albumId = a.id AND p.state = 'published') as publishedPhotoCount,
                   (SELECT p.imageLink FROM photos p WHERE p.albumId = a.id ORDER BY p.createdAt DESC LIMIT 1) as latestPhoto
            FROM albums a
            JOIN users u ON a.publisherId = u.id
            WHERE a.publisherId = ?
        ";
        
        $params = [$userId];
        
        if (!$includePrivate) {
            $query .= " AND a.public = TRUE";
        }
        
        $query .= " ORDER BY a.updatedAt DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $albums = $stmt->fetchAll();
        
        $countQuery = "SELECT COUNT(*) as total FROM albums WHERE publisherId = ?";
        if (!$includePrivate) {
            $countQuery .= " AND public = TRUE";
        }
        
        $stmt = $this->db->prepare($countQuery);
        $stmt->execute([$userId]);
        $totalResult = $stmt->fetch();
        $totalAlbums = $totalResult['total'];
        
        foreach ($albums as &$album) {
            $album['stats'] = [
                'published_photos' => (int)$album['publishedPhotoCount'],
                'has_latest_photo' => !empty($album['latestPhoto'])
            ];
            unset($album['publishedPhotoCount']);
        }
        
        return [
            'albums' => $albums,
            'pagination' => [
                'current_page' => (int)$page,
                'per_page' => (int)$limit,
                'total_items' => (int)$totalAlbums,
                'total_pages' => ceil($totalAlbums / $limit)
            ]
        ];
    }
    
    public function updateAlbum(int $albumId, int $userId, array $data): bool {
        $stmt = $this->db->prepare("SELECT id, publisherId FROM albums WHERE id = ?");
        $stmt->execute([$albumId]);
        $album = $stmt->fetch();
        
        if (!$album) {
            throw new Exception("Album non trouvé");
        }
        
        if ($album['publisherId'] != $userId) {
            throw new Exception("Vous n'êtes pas autorisé à modifier cet album");
        }
        
        if (isset($data['isPrivate'])) {
            $isPrivate = $data['isPrivate'];
            unset($data['isPrivate']);
            
            if ($isPrivate) {
                $user = $this->getUserWithPermissions($userId);
                if (!$this->userCanCreatePrivateAlbum($user)) {
                    throw new Exception("Seuls les utilisateurs Pro et supérieurs peuvent créer des albums privés");
                }
            }
            $data['public'] = !$isPrivate;
        }
        
        if (isset($data['name'])) {
            $stmt = $this->db->prepare("SELECT id FROM albums WHERE name = ? AND publisherId = ? AND id != ?");
            $stmt->execute([$data['name'], $userId, $albumId]);
            if ($stmt->fetch()) {
                throw new Exception("Un album avec ce titre existe déjà pour cet utilisateur");
            }
        }
        
        if (empty($data)) {
            return false;
        }
        
        $setParts = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $setParts[] = "$key = ?";
            $params[] = $value;
        }
        
        $params[] = $albumId;
        
        $query = "UPDATE albums SET " . implode(', ', $setParts) . ", updatedAt = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute($params);
    }
    
    public function deleteAlbum(int $albumId, int $userId): bool {
        $stmt = $this->db->prepare("SELECT id, publisherId, name FROM albums WHERE id = ?");
        $stmt->execute([$albumId]);
        $album = $stmt->fetch();
        
        if (!$album) {
            throw new Exception("Album non trouvé");
        }
        
        if ($album['publisherId'] != $userId) {
            throw new Exception("Vous n'êtes pas autorisé à supprimer cet album");
        }
        
        $this->logAlbumDeletion($albumId, $album['name'], $userId);
        
        $stmt = $this->db->prepare("DELETE FROM albums WHERE id = ?");
        
        return $stmt->execute([$albumId]);
    }
    
    private function getUserWithPermissions(int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT u.*, 
                   CASE 
                       WHEN u.level = 'administrator' THEN 'administrator'
                       WHEN u.level = 'moderator' THEN 'moderator'
                       WHEN u.subscriptionEnd IS NOT NULL AND u.subscriptionEnd > NOW() THEN 'pro'
                       ELSE 'basic'
                   END as user_type
            FROM users u
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    private function userCanCreatePrivateAlbum(array $user): bool {
        if (!$user) {
            return false;
        }
        
        $userType = $user['user_type'] ?? 'basic';
        
        return in_array($userType, ['pro', 'moderator', 'administrator']);
    }
    
    private function updateAlbumPhotoCount(int $albumId): void {
        $stmt = $this->db->prepare("
            UPDATE albums 
            SET photoCount = (
                SELECT COUNT(*) 
                FROM photos 
                WHERE albumId = ? AND state = 'published'
            )
            WHERE id = ?
        ");
        $stmt->execute([$albumId, $albumId]);
    }
    
  
    
    public function albumExists(int $albumId): bool {
        $stmt = $this->db->prepare("SELECT id FROM albums WHERE id = ?");
        $stmt->execute([$albumId]);
        return (bool)$stmt->fetch();
    }
    
    public function getAlbumStats(int $albumId): array {
        $stmt = $this->db->prepare("
            SELECT 
                a.photoCount,
                COUNT(DISTINCT p.userId) as unique_contributors,
                MIN(p.createdAt) as first_photo_date,
                MAX(p.createdAt) as last_photo_date,
                AVG(p.viewCount) as avg_views
            FROM albums a
            LEFT JOIN photos p ON a.id = p.albumId AND p.state = 'published'
            WHERE a.id = ?
            GROUP BY a.id
        ");
        $stmt->execute([$albumId]);
        
        $stats = $stmt->fetch() ?: [];
        
        $stmt = $this->db->prepare("
            SELECT COUNT(l.photoId) as total_likes
            FROM likes l
            JOIN photos p ON l.photoId = p.id
            WHERE p.albumId = ? AND p.state = 'published'
        ");
        $stmt->execute([$albumId]);
        $likes = $stmt->fetch();
        
        $stats['total_likes'] = $likes['total_likes'] ?? 0;
        
        return $stats;
    }
}