<?php

class Album {
    private int $id;
    private string $name;
    private bool $public;
    private ?string $cover;
    private int $photoCount;
    private ?string $updatedAt;
    private int $publisherId;
    
    public function __construct(array $data) {
        $this->id = $data['id'] ?? 0;
        $this->name = $data['name'] ?? '';
        $this->public = (bool)($data['public'] ?? true);
        $this->cover = $data['cover'] ?? null;
        $this->photoCount = $data['photoCount'] ?? 0;
        $this->updatedAt = $data['updatedAt'] ?? null;
        $this->publisherId = $data['publisherId'] ?? 0;
    }
    
    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function isPublic(): bool { return $this->public; }
    public function getCover(): ?string { return $this->cover; }
    public function getPhotoCount(): int { return $this->photoCount; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }
    public function getPublisherId(): int { return $this->publisherId; }
    
    // Setters
    public function setName(string $name): void { $this->name = $name; }
    public function setPublic(bool $public): void { $this->public = $public; }
    public function setCover(?string $cover): void { $this->cover = $cover; }
    public function setPhotoCount(int $count): void { $this->photoCount = $count; }
    public function incrementPhotoCount(): void { $this->photoCount++; }
    public function decrementPhotoCount(): void { $this->photoCount = max(0, $this->photoCount - 1); }
    
    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'public' => $this->public,
            'cover' => $this->cover,
            'photoCount' => $this->photoCount,
            'updatedAt' => $this->updatedAt,
            'publisherId' => $this->publisherId
        ];
    }
}