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
    private ?\DateTime $publishedAt = null;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;
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
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
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
    public function getPublishedAt(): ?\DateTime { return $this->publishedAt; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function getUserId(): int { return $this->userId; }
    public function getAlbumId(): ?int { return $this->albumId; }
    
    // Setters with validation
    public function setTitle(string $title): void
    {
        if (strlen($title) < 2 || strlen($title) > 200) {
            throw new \InvalidArgumentException("Title must be between 2 and 200 characters");
        }
        $this->title = $title;
        $this->updatedAt = new \DateTime();
    }
    
    public function setDescription(?string $description): void
    {
        if ($description !== null && strlen($description) > 2000) {
            throw new \InvalidArgumentException("Description must not exceed 2000 characters");
        }
        $this->description = $description;
        $this->updatedAt = new \DateTime();
    }
    
    public function setImageLink(string $imageLink): void
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
        $this->imageLink = $imageLink;
        $this->updatedAt = new \DateTime();
    }
    
    public function setFileSize(int $fileSize): void
    {
        $maxSize = 10 * 1024 * 1024;
        if ($fileSize > $maxSize) {
            throw new \InvalidArgumentException("File size must not exceed 10 MB");
        }
        $this->fileSize = $fileSize;
    }
    
    public function setDimensions(string $dimensions): void
    {
        if (!preg_match('/^\d+x\d+$/', $dimensions)) {
            throw new \InvalidArgumentException("Dimensions must be in format 'widthxheight'");
        }
        $this->dimensions = $dimensions;
    }
    
    public function setState(string $state): void
    {
        $allowedStates = ['draft', 'published', 'archived'];
        if (!in_array($state, $allowedStates)) {
            throw new \InvalidArgumentException("Invalid photo state");
        }
        
        if ($state === 'published' && $this->state !== 'published') {
            $this->publishedAt = new \DateTime();
        }
        
        $this->state = $state;
        $this->updatedAt = new \DateTime();
    }
    
    public function setAlbumId(?int $albumId): void
    {
        $this->albumId = $albumId;
        $this->updatedAt = new \DateTime();
    }
    
    public function incrementViewCount(): void
    {
        $this->viewCount++;
        $this->updatedAt = new \DateTime();
    }
    
    public function addTag(Tag $tag): void
    {
        if (!in_array($tag, $this->tags, true)) {
            $this->tags[] = $tag;
        }
    }
    
    public function removeTag(Tag $tag): void
    {
        $this->tags = array_filter($this->tags, function($t) use ($tag) {
            return $t !== $tag;
        });
    }
    
    public function getTags(): array
    {
        return $this->tags;
    }
    
    public function addComment(Comment $comment): void
    {
        $this->comments[] = $comment;
    }
    
    public function removeComment(Comment $comment): void
    {
        $this->comments = array_filter($this->comments, function($c) use ($comment) {
            return $c !== $comment;
        });
    }
    
    public function getComments(): array
    {
        return $this->comments;
    }
    
    public function addLike(Like $like): void
    {
        $this->likes[] = $like;
    }
    
    public function removeLike(Like $like): void
    {
        $this->likes = array_filter($this->likes, function($l) use ($like) {
            return $l !== $like;
        });
    }
    
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