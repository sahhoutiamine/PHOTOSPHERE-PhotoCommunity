<?php
// Comment.php

class Comment
{
    private int $id;
    private string $content;
    private bool $isArchive = false;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;
    private int $userId;
    private int $photoId;
    private ?int $parentId = null;
    
    // Associations
    private ?User $author = null;
    private ?Photo $photo = null;
    
    public function __construct(
        string $content,
        int $userId,
        int $photoId,
        ?int $parentId = null
    ) {
        $this->setContent($content);
        $this->userId = $userId;
        $this->photoId = $photoId;
        $this->parentId = $parentId;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }
    
    // Getters
    public function getId(): int { return $this->id; }
    public function getContent(): string { return $this->content; }
    public function isArchive(): bool { return $this->isArchive; }
    public function getUserId(): int { return $this->userId; }
    public function getPhotoId(): int { return $this->photoId; }
    public function getParentId(): ?int { return $this->parentId; }
    
    /

    
    public function getAuthor(): ?User
    {
        return $this->author;
    }
    
    public function setPhoto(Photo $photo): void
    {
        $this->photo = $photo;
        $this->photoId = $photo->getId();
    }
    
    public function getPhoto(): ?Photo
    {
        return $this->photo;
    }
    
    
}