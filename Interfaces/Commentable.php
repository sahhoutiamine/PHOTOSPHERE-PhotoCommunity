<?php 

interface Commentable {
    public function addComment(string $content, int $userId): int;
    public function removeComment(int $commentId): bool;
    public function getComments(): array;
    public function getCommentCount(): int;
}

