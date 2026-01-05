<?php
// Photo.php

class Photo
{
    private int $id;
    private string $title;
    private ?string $description;
    private string $imageLink;
    private int $fileSize;
    private string $dimensions;
    private string $state = 'draft';
    private int $viewCount = 0;
    private ?string $publishedAt = null;
    private int $userId;
    private ?int $albumId = null;
    
    // Associations
    private ?User $owner = null;
    private ?Album $album = null;
    private array $tags = [];
    private array $comments = [];
    private array $likes = [];
    
    public function __construct(
        string $title,
        string $imageLink,
        int $userId,
        int $fileSize,
        string $dimensions
    ) {
        $this->setTitle($title);
        $this->setImageLink($imageLink);
        $this->userId = $userId;
        $this->setFileSize($fileSize);
        $this->setDimensions($dimensions);
        
    }
    
    // Getters
    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getImageLink(): string { return $this->imageLink; }
    public function getFileSize(): int { return $this->fileSize; }
    public function getDimensions(): string { return $this->dimensions; }
    public function getState(): string { return $this->state; }
    public function getViewCount(): int { return $this->viewCount; }
    public function getPublishedAt(): ?string { return $this->publishedAt; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }
    public function getUserId(): int { return $this->userId; }
    public function getAlbumId(): ?int { return $this->albumId; }
    
    public function getLikes(): array
    {
        return $this->likes;
    }
    
    public function getLikeCount(): int
    {
        return count($this->likes);
    }
    
    public function isOwnedBy(User $user): bool
    {
        return $this->userId === $user->getId();
    }
    
    public function isPublic(): bool
    {
        return $this->state === 'published';
    }
}