<?php 

interface Likeable {
    public function addLike(int $userId): bool;
    public function removeLike(int $userId): bool;
    public function isLikedBy(int $userId): bool;
    public function getLikeCount(): int;
    public function getLikedBy(): array;
}


