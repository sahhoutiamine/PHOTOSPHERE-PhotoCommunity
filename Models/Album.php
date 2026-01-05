<?php
// Album.php

class Album
{
    private int $id;
    private string $name;
    private bool $public = true;
    private ?string $cover = null;
    private int $photoCount = 0;
    private \DateTime $updatedAt;
    private int $publisherId;
    
    // Associations
    private ?User $publisher = null;
    private array $photos = [];
    
    public function __construct(
        string $name,
        int $publisherId,
        bool $isPublic = true
    ) {
        $this->setName($name);
        $this->publisherId = $publisherId;
        $this->public = $isPublic;
        $this->updatedAt = new \DateTime();
    }
    
    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function isPublic(): bool { return $this->public; }
    public function getCover(): ?string { return $this->cover; }
    public function getPhotoCount(): int { return $this->photoCount; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function getPublisherId(): int { return $this->publisherId; }
    
    // Setters with validation
    public function setName(string $name): void
    {
        if (strlen($name) > 255) {
            throw new \InvalidArgumentException("Album name must not exceed 255 characters");
        }
        $this->name = $name;
        $this->updatedAt = new \DateTime();
    }
    
    public function setPublic(bool $isPublic, User $user): void
    {
        if (!$isPublic && !$user->canCreatePrivateAlbum()) {
            throw new \Exception("User cannot create private albums");
        }
        $this->public = $isPublic;
        $this->updatedAt = new \DateTime();
    }
    
    public function setCover(?string $cover): void
    {
        $this->cover = $cover;
        $this->updatedAt = new \DateTime();
    }
    
    // Association methods
    public function addPhoto(Photo $photo): void
    {
        if (count($this->photos) >= 100) {
            throw new \Exception("Album cannot contain more than 100 photos");
        }
        
        if (!in_array($photo, $this->photos, true)) {
            $this->photos[] = $photo;
            $this->photoCount++;
            $this->updatedAt = new \DateTime();
            
            $photo->setAlbumId($this->id);
        }
    }
    
    public function removePhoto(Photo $photo): void
    {
        $this->photos = array_filter($this->photos, function($p) use ($photo) {
            return $p !== $photo;
        });
        
        $this->photoCount = count($this->photos);
        $this->updatedAt = new \DateTime();
        
        $photo->setAlbumId(null);
    }
    
    public function getPhotos(): array
    {
        return $this->photos;
    }
    
    public function hasPhoto(Photo $photo): bool
    {
        return in_array($photo, $this->photos, true);
    }
    
    public function isValid(): bool
    {
        return $this->photoCount >= 1;
    }
    
    public function setPublisher(User $user): void
    {
        $this->publisher = $user;
        $this->publisherId = $user->getId();
    }
    
    public function getPublisher(): ?User
    {
        return $this->publisher;
    }
}