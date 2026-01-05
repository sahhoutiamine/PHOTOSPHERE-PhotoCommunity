<?php

class Like {
    private int $userId;
    private int $photoId;
    private ?string $createdAt;
    
    public function __construct(array $data) {
        $this->userId = $data['userId'] ?? 0;
        $this->photoId = $data['photoId'] ?? 0;
        $this->createdAt = $data['createdAt'] ?? null;
    }
    
    // Getters
    public function getUserId(): int { return $this->userId; }
    public function getPhotoId(): int { return $this->photoId; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    
    public function toArray(): array {
        return [
            'userId' => $this->userId,
            'photoId' => $this->photoId,
            'createdAt' => $this->createdAt
        ];
    }
}