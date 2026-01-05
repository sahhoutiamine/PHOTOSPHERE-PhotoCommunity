<?php
// User.php

abstract class User
{
    protected int $id;
    protected string $username;
    protected string $email;
    protected string $passwordHash;
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
    }
    
    // Getters
    public function getId(): int { return $this->id; }
    public function getUsername(): string { return $this->username; }
    public function getEmail(): string { return $this->email; }
    public function getBio(): ?string { return $this->bio; }
    public function getProfilePicture(): ?string { return $this->profilePicture; }
    public function getUploadCount(): int { return $this->uploadCount; }
    public function getLevel(): ?string { return $this->level; }
    public function isSuper(): bool { return $this->isSuper; }
    
    
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