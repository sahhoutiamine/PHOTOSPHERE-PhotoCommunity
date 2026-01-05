<?php
// PhotoTag.php

class PhotoTag
{
    private int $photoId;
    private int $tagId;
    
    public function __construct(int $photoId, int $tagId)
    {
        $this->photoId = $photoId;
        $this->tagId = $tagId;
    }
    
    // Getters
    public function getPhotoId(): int { return $this->photoId; }
    public function getTagId(): int { return $this->tagId; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    
    public static function createAssociation(Photo $photo, Tag $tag): self
    {
        return new self($photo->getId(), $tag->getId());
    }
}