<?php
require_once __DIR__ . '/../Models/BasicUser.php';
require_once __DIR__ . '/../Models/ProUser.php';
require_once __DIR__ . '/../Models/Moderator.php';
require_once __DIR__ . '/../Models/Administrator.php';

class UserFactory {
    public static function createFromArray(array $data): User {
        if (isset($data['isSuper']) && $data['isSuper']) {
            return new Administrator($data);
        }
        
        $role = $data['role'] ?? 'BasicUser';
        
        switch ($role) {
            case 'Administrator':
                return new Administrator($data);
            
            case 'Moderator':
                return new Moderator($data);
            
            case 'ProUser':
                return new ProUser($data);
            
            case 'BasicUser':
            default:
                return new BasicUser($data);
        }
    }
    
    public static function createBasicUser(string $username, string $email, string $password): BasicUser {
        return new BasicUser([
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 'BasicUser',
            'isSuper' => false
        ]);
    }
    
    public static function createProUser(string $username, string $email, string $password, string $subscriptionEnd): ProUser {
        return new ProUser([
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 'ProUser',
            'subscriptionStart' => date('Y-m-d H:i:s'),
            'subscriptionEnd' => $subscriptionEnd,
            'isSuper' => false
        ]);
    }
    
    public static function createModerator(string $username, string $email, string $password, string $moderatorLevel = 'junior'): Moderator {
        return new Moderator([
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 'Moderator',
            'moderatorLevel' => $moderatorLevel,
            'isSuper' => false
        ]);
    }
    
    public static function createAdministrator(string $username, string $email, string $password, bool $isSuper = false): Administrator {
        return new Administrator([
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'role' => 'Administrator',
            'isSuper' => $isSuper
        ]);
    }
}