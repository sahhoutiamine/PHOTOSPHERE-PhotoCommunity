<?php

class Photo {
    private int $id;
    private ?string $title;
    private ?string $description;
    private string $imageLink;
    private ?int $fileSize;
    private ?string $dimensions;
    private ?string $state; // 'draft', 'published', 'archived'
    private int $viewCount;
    private ?string $publishedAt;
    private ?string $createdAt;
    private ?string $updatedAt;
    private int $userId;
    private ?int $albumId;
    
    public function __construct(array $data) {
        $this->id = $data['id'] ?? 0;
        $this->title = $data['title'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->imageLink = $data['imageLink'] ?? '';
        $this->fileSize = $data['fileSize'] ?? null;
        $this->dimensions = $data['dimensions'] ?? null;
        $this->state = $data['state'] ?? 'draft';
        $this->viewCount = $data['viewCount'] ?? 0;
        $this->publishedAt = $data['publishedAt'] ?? null;
        $this->createdAt = $data['createdAt'] ?? null;
        $this->updatedAt = $data['updatedAt'] ?? null;
        $this->userId = $data['userId'] ?? 0;
        $this->albumId = $data['albumId'] ?? null;
    }
    
    // Getters
    public function getId(): int { return $this->id; }
    public function getTitle(): ?string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getImageLink(): string { return $this->imageLink; }
    public function getFileSize(): ?int { return $this->fileSize; }
    public function getDimensions(): ?string { return $this->dimensions; }
    public function getState(): ?string { return $this->state; }
    public function getViewCount(): int { return $this->viewCount; }
    public function getPublishedAt(): ?string { return $this->publishedAt; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function getUserId(): int { return $this->userId; }
    public function getAlbumId(): ?int { return $this->albumId; }
    
    // Setters
    public function setTitle(?string $title): void { $this->title = $title; }
    public function setDescription(?string $description): void { $this->description = $description; }
    public function setState(string $state): void { $this->state = $state; }
    public function setAlbumId(?int $albumId): void { $this->albumId = $albumId; }
    public function incrementViewCount(): void { $this->viewCount++; }
    
    public function publish(): void {
        $this->state = 'published';
        $this->publishedAt = date('Y-m-d H:i:s');
    }
    
    public function archive(): void {
        $this->state = 'archived';
    }
    
    public function isDraft(): bool {
        return $this->state === 'draft';
    }
    
    public function isPublished(): bool {
        return $this->state === 'published';
    }
    
    public function toArray(): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'imageLink' => $this->imageLink,
            'fileSize' => $this->fileSize,
            'dimensions' => $this->dimensions,
            'state' => $this->state,
            'viewCount' => $this->viewCount,
            'publishedAt' => $this->publishedAt,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'userId' => $this->userId,
            'albumId' => $this->albumId
        ];
    }
}