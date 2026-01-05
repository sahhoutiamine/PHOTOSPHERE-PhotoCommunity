<?php

class Comment {
    private int $id;
    private string $content;
    private bool $isArchive;
    private ?string $createdAt;
    private ?string $updatedAt;
    private int $userId;
    private int $photoId;
    
    public function __construct(array $data) {
        $this->id = $data['id'] ?? 0;
        $this->content = $data['content'] ?? '';
        $this->isArchive = (bool)($data['isArchive'] ?? false);
        $this->createdAt = $data['createdAt'] ?? null;
        $this->updatedAt = $data['updatedAt'] ?? null;
        $this->userId = $data['userId'] ?? 0;
        $this->photoId = $data['photoId'] ?? 0;
    }
    
    // Getters
    public function getId(): int { return $this->id; }
    public function getContent(): string { return $this->content; }
    public function isArchived(): bool { return $this->isArchive; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function getUserId(): int { return $this->userId; }
    public function getPhotoId(): int { return $this->photoId; }
    
    // Setters
    public function setContent(string $content): void { $this->content = $content; }
    public function archive(): void { $this->isArchive = true; }
    
    public function toArray(): array {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'isArchive' => $this->isArchive,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'userId' => $this->userId,
            'photoId' => $this->photoId
        ];
    }
}