<?php 

interface Taggable {
    public function addTag(string $tag): void;
    public function removeTag(string $tag): void;
    public function getTags(): array;
    public function hasTag(string $tag): bool;
    public function clearTags(): void;
}

