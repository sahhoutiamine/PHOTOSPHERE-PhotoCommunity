<?php

require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Album.php';
require_once __DIR__ . '/../Models/Photo.php';

class AlbumRepository {
    
    private PDO $db;
    private const MAX_PHOTOS_PER_ALBUM = 100;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function createAlbum(int $userId, string $title, string $description, bool $isPrivate): int {
        // Vérifier si l'utilisateur a le droit de créer un album privé
        if ($isPrivate) {
            $user = $this->getUserWithPermissions($userId);
            if (!$this->userCanCreatePrivateAlbum($user)) {
                throw new Exception("Seuls les utilisateurs Pro et supérieurs peuvent créer des albums privés");
            }
        }
        
        // Vérifier l'unicité du titre pour cet utilisateur
        $stmt = $this->db->prepare("SELECT id FROM albums WHERE name = ? AND publisherId = ?");
        $stmt->execute([$title, $userId]);
        if ($stmt->fetch()) {
            throw new Exception("Un album avec ce titre existe déjà pour cet utilisateur");
        }
        
        // Insérer l'album
        $stmt = $this->db->prepare("
            INSERT INTO albums (name, description, public, publisherId, createdAt, updatedAt)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        
        $public = !$isPrivate;
        $stmt->execute([$title, $description, $public, $userId]);
        
        return (int)$this->db->lastInsertId();
    }
    
    public function addPhotoToAlbum(int $albumId, int $photoId, int $userId): bool {
        // Vérifier la propriété de l'album
        $stmt = $this->db->prepare("SELECT id FROM albums WHERE id = ? AND publisherId = ?");
        $stmt->execute([$albumId, $userId]);
        if (!$stmt->fetch()) {
            throw new Exception("Album non trouvé ou vous n'en êtes pas le propriétaire");
        }
        
        // Vérifier la propriété de la photo
        $stmt = $this->db->prepare("SELECT id FROM photos WHERE id = ? AND userId = ?");
        $stmt->execute([$photoId, $userId]);
        if (!$stmt->fetch()) {
            throw new Exception("Photo non trouvée ou vous n'en êtes pas le propriétaire");
        }
        
        // Vérifier si la photo est déjà dans l'album
        $stmt = $this->db->prepare("SELECT photoId FROM photos WHERE id = ? AND albumId = ?");
        $stmt->execute([$photoId, $albumId]);
        if ($stmt->fetch()) {
            throw new Exception("Cette photo est déjà dans l'album");
        }
        
        // Vérifier la limite de photos par album
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM photos WHERE albumId = ?");
        $stmt->execute([$albumId]);
        $result = $stmt->fetch();
        if ($result['count'] >= self::MAX_PHOTOS_PER_ALBUM) {
            throw new Exception("L'album ne peut contenir plus de " . self::MAX_PHOTOS_PER_ALBUM . " photos");
        }
        
        // Ajouter la photo à l'album
        $stmt = $this->db->prepare("UPDATE photos SET albumId = ? WHERE id = ?");
        $success = $stmt->execute([$albumId, $photoId]);
        
        if ($success) {
            // Mettre à jour le compteur de photos de l'album
            $this->updateAlbumPhotoCount($albumId);
            
            // Mettre à jour la date de modification de l'album
            $stmt = $this->db->prepare("UPDATE albums SET updatedAt = NOW() WHERE id = ?");
            $stmt->execute([$albumId]);
        }
        
        return $success;
    }
    
    public function removePhotoFromAlbum(int $albumId, int $photoId, int $userId): bool {
        // Vérifier la propriété de l'album
        $stmt = $this->db->prepare("SELECT id FROM albums WHERE id = ? AND publisherId = ?");
        $stmt->execute([$albumId, $userId]);
        if (!$stmt->fetch()) {
            throw new Exception("Album non trouvé ou vous n'en êtes pas le propriétaire");
        }
        
        // Vérifier que la photo est bien dans l'album
        $stmt = $this->db->prepare("SELECT id FROM photos WHERE id = ? AND albumId = ?");
        $stmt->execute([$photoId, $albumId]);
        if (!$stmt->fetch()) {
            throw new Exception("Cette photo n'est pas dans l'album");
        }
        
        // Retirer la photo de l'album (définir albumId à NULL)
        $stmt = $this->db->prepare("UPDATE photos SET albumId = NULL WHERE id = ?");
        $success = $stmt->execute([$photoId]);
        
        if ($success) {
            // Mettre à jour le compteur de photos de l'album
            $this->updateAlbumPhotoCount($albumId);
            
            // Mettre à jour la date de modification de l'album
            $stmt = $this->db->prepare("UPDATE albums SET updatedAt = NOW() WHERE id = ?");
            $stmt->execute([$albumId]);
        }
        
        return $success;
    }
    
    public function getAlbumWithPhotos(int $albumId, int $userId): ?array {
        // Récupérer les informations de l'album
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
        
        // Vérifier la visibilité
        if (!$album['public'] && $album['publisherId'] != $userId) {
            throw new Exception("Cet album est privé et vous n'êtes pas autorisé à le consulter");
        }
        
        // Récupérer les photos avec pagination (par défaut 20 photos)
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
        
        // Récupérer le nombre total de photos pour la pagination
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
        
        // Construire la requête en fonction des paramètres
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
        
        // Récupérer le nombre total d'albums
        $countQuery = "SELECT COUNT(*) as total FROM albums WHERE publisherId = ?";
        if (!$includePrivate) {
            $countQuery .= " AND public = TRUE";
        }
        
        $stmt = $this->db->prepare($countQuery);
        $stmt->execute([$userId]);
        $totalResult = $stmt->fetch();
        $totalAlbums = $totalResult['total'];
        
        // Formater les résultats
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
        // Vérifier la propriété de l'album
        $stmt = $this->db->prepare("SELECT id, publisherId FROM albums WHERE id = ?");
        $stmt->execute([$albumId]);
        $album = $stmt->fetch();
        
        if (!$album) {
            throw new Exception("Album non trouvé");
        }
        
        if ($album['publisherId'] != $userId) {
            throw new Exception("Vous n'êtes pas autorisé à modifier cet album");
        }
        
        // Vérifier les permissions pour les albums privés
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
        
        // Vérifier l'unicité du titre si modifié
        if (isset($data['name'])) {
            $stmt = $this->db->prepare("SELECT id FROM albums WHERE name = ? AND publisherId = ? AND id != ?");
            $stmt->execute([$data['name'], $userId, $albumId]);
            if ($stmt->fetch()) {
                throw new Exception("Un album avec ce titre existe déjà pour cet utilisateur");
            }
        }
        
        // Construire la requête de mise à jour
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
        // Vérifier la propriété de l'album
        $stmt = $this->db->prepare("SELECT id, publisherId, name FROM albums WHERE id = ?");
        $stmt->execute([$albumId]);
        $album = $stmt->fetch();
        
        if (!$album) {
            throw new Exception("Album non trouvé");
        }
        
        if ($album['publisherId'] != $userId) {
            throw new Exception("Vous n'êtes pas autorisé à supprimer cet album");
        }
        
        // Log de suppression (vous pourriez l'enregistrer dans une table de logs)
        $this->logAlbumDeletion($albumId, $album['name'], $userId);
        
        // La suppression cascade via la contrainte SQL ON DELETE SET NULL pour les photos
        // et ON DELETE CASCADE pour l'album lui-même
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
        
        // Seuls les utilisateurs Pro, Moderator et Administrator peuvent créer des albums privés
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
    
    private function logAlbumDeletion(int $albumId, string $albumName, int $userId): void {
        // Vous pourriez implémenter une table de logs comme ceci :
        // CREATE TABLE deletion_logs (
        //     id INT AUTO_INCREMENT PRIMARY KEY,
        //     entity_type VARCHAR(50),
        //     entity_id INT,
        //     entity_name VARCHAR(255),
        //     deleted_by INT,
        //     deleted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        //     FOREIGN KEY (deleted_by) REFERENCES users(id)
        // );
        
        // Pour l'instant, on se contente d'un simple log dans un fichier
        $logMessage = sprintf(
            "[%s] Album deleted - ID: %d, Name: %s, Deleted by user ID: %d\n",
            date('Y-m-d H:i:s'),
            $albumId,
            $albumName,
            $userId
        );
        
        // Enregistrer dans un fichier de logs (assurez-vous que le dossier existe)
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logDir . '/album_deletions.log', $logMessage, FILE_APPEND);
    }
    
    // Méthode utilitaire pour vérifier si un album existe
    public function albumExists(int $albumId): bool {
        $stmt = $this->db->prepare("SELECT id FROM albums WHERE id = ?");
        $stmt->execute([$albumId]);
        return (bool)$stmt->fetch();
    }
    
    // Méthode pour récupérer les statistiques d'un album
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
        
        // Compter les likes totaux
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