<?php

declare(strict_types=1);

trait TaggableTrait
{
    protected array $tags = [];
    protected bool $tagsLoaded = false;
    
    public function addTag(string $tag): void
    {
        $this->ensureTagsLoaded();
        $normalizedTag = $this->normalizeTag($tag);
        
        if (!in_array($normalizedTag, $this->tags, true)) {
            $this->tags[] = $normalizedTag;
        }
    }
    
    public function removeTag(string $tag): void
    {
        $this->ensureTagsLoaded();
        $normalizedTag = $this->normalizeTag($tag);
        $key = array_search($normalizedTag, $this->tags, true);
        
        if ($key !== false) {
            unset($this->tags[$key]);
            $this->tags = array_values($this->tags);
        }
    }
    
    public function getTags(): array
    {
        $this->ensureTagsLoaded();
        return $this->tags;
    }
    
    public function hasTag(string $tag): bool
    {
        $this->ensureTagsLoaded();
        $normalizedTag = $this->normalizeTag($tag);
        return in_array($normalizedTag, $this->tags, true);
    }
    
    public function clearTags(): void
    {
        $this->tags = [];
        $this->tagsLoaded = true;
    }
    
    protected function normalizeTag(string $tag): string
    {
        return strtolower(trim($tag));
    }
    
    public function hasAllTags(array $tags): bool
    {
        $this->ensureTagsLoaded();
        foreach ($tags as $tag) {
            if (!$this->hasTag($tag)) {
                return false;
            }
        }
        return true;
    }
    
    public function hasAnyTag(array $tags): bool
    {
        $this->ensureTagsLoaded();
        foreach ($tags as $tag) {
            if ($this->hasTag($tag)) {
                return true;
            }
        }
        return false;
    }
    
    protected function ensureTagsLoaded(): void
    {
        if (!$this->tagsLoaded) {
            $this->loadTagsFromDatabase();
            $this->tagsLoaded = true;
        }
    }
    
    abstract protected function loadTagsFromDatabase(): void;
}