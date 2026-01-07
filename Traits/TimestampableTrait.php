<?php

declare(strict_types=1);

trait TimestampableTrait
{
    protected ?DateTimeInterface $createdAt = null;
    protected ?DateTimeInterface $updatedAt = null;
    
    public function initializeTimestamps(): void
    {
        $now = new DateTime();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }
    
    public function updateTimestamps(): void
    {
        $this->updatedAt = new DateTime();
    }
    
    public function getCreatedAt(?string $format = null): DateTimeInterface|string|null
    {
        if ($this->createdAt === null) {
            return null;
        }
        
        if ($format !== null) {
            return $this->createdAt->format($format);
        }
        
        return $this->createdAt;
    }
    
    public function getUpdatedAt(?string $format = null): DateTimeInterface|string|null
    {
        if ($this->updatedAt === null) {
            return null;
        }
        
        if ($format !== null) {
            return $this->updatedAt->format($format);
        }
        
        return $this->updatedAt;
    }
    
    public function setCreatedAt(DateTimeInterface|string|null $createdAt): void
    {
        if (is_string($createdAt)) {
            $this->createdAt = new DateTime($createdAt);
        } else {
            $this->createdAt = $createdAt;
        }
    }
    
    public function setUpdatedAt(DateTimeInterface|string|null $updatedAt): void
    {
        if (is_string($updatedAt)) {
            $this->updatedAt = new DateTime($updatedAt);
        } else {
            $this->updatedAt = $updatedAt;
        }
    }
}