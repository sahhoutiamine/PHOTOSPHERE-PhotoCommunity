<?php
// Tag.php

class Tag
{
    private int $id;
    private string $slug;
    private int $photoCount = 0;
    
    // Association
    private array $photos = [];
    
    public function __construct(string $slug)
    {
        $this->setSlug($slug);
    }
    
    // Getters
    public function getId(): int { return $this->id; }
    public function getSlug(): string { return $this->slug; }
    public function getPhotoCount(): int { return $this->photoCount; }
    
    // Setters with validation
    public function setSlug(string $slug): void
    {
        $slug = $this->normalizeSlug($slug);
        
        if (strlen($slug) > 100) {
            throw new \InvalidArgumentException("Tag slug must not exceed 100 characters");
        }
        
        $this->slug = $slug;
    }
    
    private function normalizeSlug(string $slug): string
    {
        $slug = strtolower($slug);
        
        $slug = trim($slug);
        
        $slug = preg_replace('/\s+/', '-', $slug);
        
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        
        $slug = preg_replace('/\-+/', '-', $slug);
        
        return $slug;
    }
    
    // Association methods
    public function addPhoto(Photo $photo): void
    {
        if (!in_array($photo, $this->photos, true)) {
            $this->photos[] = $photo;
            $this->photoCount++;
            $photo->addTag($this);
        }
    }
    
    public function removePhoto(Photo $photo): void
    {
        $this->photos = array_filter($this->photos, function($p) use ($photo) {
            return $p !== $photo;
        });
        
        $this->photoCount = count($this->photos);
        $photo->removeTag($this);
    }
    
    public function getPhotos(): array
    {
        return $this->photos;
    }
    
    public function hasPhoto(Photo $photo): bool
    {
        return in_array($photo, $this->photos, true);
    }
}