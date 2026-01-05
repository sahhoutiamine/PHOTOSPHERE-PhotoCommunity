<?php
// Like.php

class Like
{
    private int $userId;
    private int $photoId;
    
    // Associations
    private ?User $user = null;
    private ?Photo $photo = null;
    
    public function __construct(int $userId, int $photoId)
    {
        $this->userId = $userId;
        $this->photoId = $photoId;
    }
    
    // Getters
    public function getUserId(): int { return $this->userId; }
    public function getPhotoId(): int { return $this->photoId; }
    
    public function setUser(User $user): void
    {
        $this->user = $user;
    }
    
    public function getUser(): ?User
    {
        return $this->user;
    }
    
    public function setPhoto(Photo $photo): void
    {
        $this->photo = $photo;
    }
    
    public function getPhoto(): ?Photo
    {
        return $this->photo;
    }
    
    
}