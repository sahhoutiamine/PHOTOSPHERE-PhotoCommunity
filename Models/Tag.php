<?php

class Tag {
    private int $id;
    private string $slug;
    private int $photoCount;
    
    public function __construct(array $data) {
        $this->id = $data['id'] ?? 0;
        $this->slug = $data['slug'] ?? '';
        $this->photoCount = $data['photoCount'] ?? 0;
    }
    
    // Getters
    public function getId(): int { return $this->id; }
    public function getSlug(): string { return $this->slug; }
    public function getPhotoCount(): int { return $this->photoCount; }
    
    // Setters
    public function setPhotoCount(int $count): void { $this->photoCount = $count; }
    public function incrementPhotoCount(): void { $this->photoCount++; }
    public function decrementPhotoCount(): void { $this->photoCount = max(0, $this->photoCount - 1); }
    
    public static function normalizeSlug(string $name): string {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
    
    public function toArray(): array {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'photoCount' => $this->photoCount
        ];
    }
}