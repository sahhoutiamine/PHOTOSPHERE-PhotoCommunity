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
    
    public function getPhotos(): array
    {
        return $this->photos;
    }
    
    public function hasPhoto(Photo $photo): bool
    {
        return in_array($photo, $this->photos, true);
    }
}