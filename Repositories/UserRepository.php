<?php
require_once __DIR__ . '/../Interfaces/UserRepositoryInterface.php';
require_once __DIR__ . '/../Services/UserFactory.php';

class UserRepository implements UserRepositoryInterface {
    private PDO $db;
    
    public function __construct(PDO $db) {
        $this->db = $db;
    }
    
    public function create(array $data): User {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $sql = "INSERT INTO users (username, email, password, bio, profilePicture, role, moderatorLevel, isSuper) 
                VALUES (:username, :email, :password, :bio, :profilePicture, :role, :moderatorLevel, :isSuper)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'bio' => $data['bio'] ?? null,
            'profilePicture' => $data['profilePicture'] ?? null,
            'role' => $data['role'] ?? 'BasicUser',
            'moderatorLevel' => $data['moderatorLevel'] ?? null,
            'isSuper' => $data['isSuper'] ?? 0
        ]);
        
        $data['id'] = (int)$this->db->lastInsertId();
        return UserFactory::createFromArray($data);
    }
    
    public function findById(int $id): ?User {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        return $data ? UserFactory::createFromArray($data) : null;
    }
    
    public function findByEmail(string $email): ?User {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch();
        
        return $data ? UserFactory::createFromArray($data) : null;
    }
    
    public function findByUsername(string $username): ?User {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $data = $stmt->fetch();
        
        return $data ? UserFactory::createFromArray($data) : null;
    }
    
    public function update(User $user): bool {
        $sql = "UPDATE users SET 
                username = :username, 
                email = :email, 
                bio = :bio, 
                profilePicture = :profilePicture,
                uploadCount = :uploadCount,
                lastLogin = :lastLogin,
                role = :role,
                moderatorLevel = :moderatorLevel
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'bio' => $user->getBio(),
            'profilePicture' => $user->getProfilePicture(),
            'uploadCount' => $user->getUploadCount(),
            'lastLogin' => $user->getLastLogin(),
            'role' => $user->getRole(),
            'moderatorLevel' => $user->getModeratorLevel(),
            'id' => $user->getId()
        ]);
    }
    
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function findAll(int $limit = 50, int $offset = 0): array {
        $stmt = $this->db->prepare("SELECT * FROM users LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        
        $users = [];
        while ($data = $stmt->fetch()) {
            $users[] = UserFactory::createFromArray($data);
        }
        
        return $users;
    }
    
    public function updateLastLogin(int $userId): bool {
        $stmt = $this->db->prepare("UPDATE users SET lastLogin = NOW() WHERE id = ?");
        return $stmt->execute([$userId]);
    }

     public function authenticate(string $email, string $password): ?User {
        $user = $this->findByEmail($email);
        
        if ($user && $user->verifyPassword($password)) {
            $this->updateLastLogin($user->getId());
            return $user;
        }
        
        return null;
    }
}