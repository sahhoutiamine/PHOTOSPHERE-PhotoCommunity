<?php
// Album.php

class Album
{
    private int $id;
    private string $name;
    private bool $public = true;
    private ?string $cover = null;
    private int $photoCount = 0;
    private int $publisherId;
    
    // Associations
    private ?User $publisher = null;
    private array $photos = [];
    
    public function __construct(
        string $name,
        int $publisherId,
        bool $isPublic = true
    ) {
        $this->setName($name);
        $this->publisherId = $publisherId;
        $this->public = $isPublic;
    }
    
    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function isPublic(): bool { return $this->public; }
    public function getCover(): ?string { return $this->cover; }
    public function getPhotoCount(): int { return $this->photoCount; }
    public function getPublisherId(): int { return $this->publisherId; }
    

}