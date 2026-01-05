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
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function getUserId(): int { return $this->userId; }
    public function getPhotoId(): int { return $this->photoId; }
    public function getParentId(): ?int { return $this->parentId; }
    
    // Setters with validation
    public function setContent(string $content): void
    {
        if (strlen($content) < 2 || strlen($content) > 500) {
            throw new \InvalidArgumentException("Comment must be between 2 and 500 characters");
        }
        $this->content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        $this->updatedAt = new \DateTime();
    }
    
    public function setArchive(bool $isArchive): void
    {
        $this->isArchive = $isArchive;
        $this->updatedAt = new \DateTime();
    }
    
    public function setAuthor(User $author): void
    {
        if ($this->photo !== null && $this->photo->isOwnedBy($author)) {
            throw new \Exception("Users cannot comment on their own photos");
        }
        
        $this->author = $author;
        $this->userId = $author->getId();
    }
    
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
    
    public function canBeDeletedBy(User $user): bool
    {
        if ($this->photo !== null && $this->photo->isOwnedBy($user)) {
            return true;
        }
        
        if ($this->author !== null && $this->author->getId() === $user->getId()) {
            return true;
        }
        
        return $user instanceof Moderator || $user instanceof Administrator;
    }
    
    public function setParentId(?int $parentId): void
    {
        $this->parentId = $parentId;
        $this->updatedAt = new \DateTime();
    }
}