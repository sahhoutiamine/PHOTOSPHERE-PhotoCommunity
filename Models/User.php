<?php

abstract class User {
    protected int $id;
    protected string $username;
    protected string $email;
    protected string $password;
    protected ?string $createdAt;
    protected ?string $lastLogin;
    protected ?string $bio;
    protected ?string $profilePicture;
    protected int $uploadCount;
    protected ?string $subscriptionStart;
    protected ?string $subscriptionEnd;
    protected string $role;
    protected ?string $moderatorLevel;
    protected bool $isSuper;
    
    public function __construct(array $data) {
        $this->id = $data['id'] ?? 0;
        $this->username = $data['username'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->createdAt = $data['createdAt'] ?? null;
        $this->lastLogin = $data['lastLogin'] ?? null;
        $this->bio = $data['bio'] ?? null;
        $this->profilePicture = $data['profilePicture'] ?? null;
        $this->uploadCount = $data['uploadCount'] ?? 0;
        $this->subscriptionStart = $data['subscriptionStart'] ?? null;
        $this->subscriptionEnd = $data['subscriptionEnd'] ?? null;
        $this->role = $data['role'] ?? 'BasicUser';
        $this->moderatorLevel = $data['moderatorLevel'] ?? null;
        $this->isSuper = (bool)($data['isSuper'] ?? false);
    }
    
    // Getters
    public function getId(): int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getLastLogin(): ?string { return $this->lastLogin; }
    public function getBio(): ?string { return $this->bio; }
    public function getProfilePicture(): ?string { return $this->profilePicture; }
    public function getUploadCount(): int { return $this->uploadCount; }
    public function getSubscriptionStart(): ?string { return $this->subscriptionStart; }
    public function getSubscriptionEnd(): ?string { return $this->subscriptionEnd; }
    public function getRole(): string { return $this->role; }
    public function getModeratorLevel(): ?string { return $this->moderatorLevel; }
    public function isSuper(): bool { return $this->isSuper; }
    
    // Setters
    public function setUsername(string $username): void { $this->username = $username; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setPassword(string $password): void { $this->password = password_hash($password, PASSWORD_BCRYPT); }
    public function setLastLogin(string $lastLogin): void { $this->lastLogin = $lastLogin; }
    public function setBio(?string $bio): void { $this->bio = $bio; }
    public function setProfilePicture(?string $profilePicture): void { $this->profilePicture = $profilePicture; }
    public function setUploadCount(int $uploadCount): void { $this->uploadCount = $uploadCount; }
    public function setRole(string $role): void { $this->role = $role; }
    public function setModeratorLevel(?string $moderatorLevel): void { $this->moderatorLevel = $moderatorLevel; }
    public function incrementUploadCount(): void { $this->uploadCount++; }
    public function verifyPassword(string $password): bool {
        return password_verify($password, $this->password);
    }
    
    public function setPassword(string $password): void { 
        $this->password = password_hash($password, PASSWORD_BCRYPT); 
    }
    
    abstract public function canUploadPhoto(): bool;
    abstract public function canCreatePrivateAlbum(): bool;
    abstract public function getUploadLimit(): ?int;
    
    public function toArray(): array {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'createdAt' => $this->createdAt,
            'lastLogin' => $this->lastLogin,
            'bio' => $this->bio,
            'profilePicture' => $this->profilePicture,
            'uploadCount' => $this->uploadCount,
            'subscriptionStart' => $this->subscriptionStart,
            'subscriptionEnd' => $this->subscriptionEnd,
            'role' => $this->role,
            'moderatorLevel' => $this->moderatorLevel,
            'isSuper' => $this->isSuper
        ];
    }
}