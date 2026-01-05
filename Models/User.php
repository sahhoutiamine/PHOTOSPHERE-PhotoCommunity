<?php
// User.php

abstract class User
{
    protected int $id;
    protected string $username;
    protected string $email;
    protected string $passwordHash;
    protected \DateTime $createdAt;
    protected ?\DateTime $lastLogin = null;
    protected ?string $bio = null;
    protected ?string $profilePicture = null;
    protected int $uploadCount = 0;
    protected ?string $level = null;
    protected bool $isSuper = false;
    
    public function __construct(
        string $username,
        string $email,
        string $password
    ) {
        $this->setUsername($username);
        $this->setEmail($email);
        $this->setPassword($password);
        $this->createdAt = new \DateTime();
    }
    
    // Getters
    public function getId(): int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getEmail(): string { return $this->email; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getLastLogin(): ?\DateTime { return $this->lastLogin; }
    public function getBio(): ?string { return $this->bio; }
    public function getProfilePicture(): ?string { return $this->profilePicture; }
    public function getUploadCount(): int { return $this->uploadCount; }
    public function getLevel(): ?string { return $this->level; }
    public function isSuper(): bool { return $this->isSuper; }
    
    // Setters with validation
    public function setUsername(string $username): void
    {
        if (strlen($username) < 3 || strlen($username) > 50) {
            throw new \InvalidArgumentException("Username must be between 3 and 50 characters");
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            throw new \InvalidArgumentException("Username can only contain letters, numbers and underscores");
        }
        $this->username = $username;
    }
    
    public function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email format");
        }
        $this->email = $email;
    }
    
    public function setPassword(string $password): void
    {
        if (strlen($password) < 8) {
            throw new \InvalidArgumentException("Password must be at least 8 characters long");
        }
        $this->passwordHash = password_hash($password, PASSWORD_BCRYPT);
    }
    
    public function setBio(?string $bio): void
    {
        if ($bio !== null && strlen($bio) > 1000) {
            throw new \InvalidArgumentException("Bio must not exceed 1000 characters");
        }
        $this->bio = $bio;
    }
    
    public function setProfilePicture(?string $profilePicture): void
    {
        $this->profilePicture = $profilePicture;
    }
    
    public function setLevel(?string $level): void
    {
        $this->level = $level;
    }
    
    public function setSuper(bool $isSuper): void
    {
        $this->isSuper = $isSuper;
    }
    
    public function updateLastLogin(): void
    {
        $this->lastLogin = new \DateTime();
    }
    
    public function incrementUploadCount(): void
    {
        $this->uploadCount++;
    }
    
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }
    
    abstract public function canCreatePrivateAlbum(): bool;
    abstract public function getMonthlyUploadLimit(): ?int;
}