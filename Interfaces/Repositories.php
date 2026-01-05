<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Photo.php';
require_once __DIR__ . '/../Models/Album.php';
require_once __DIR__ . '/../Models/Tag.php';
require_once __DIR__ . '/../Models/Comment.php';
require_once __DIR__ . '/../Models/Like.php';

interface UserRepositoryInterface {
    public function create(array $data): User;
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findByUsername(string $username): ?User;
    public function update(User $user): bool;
    public function delete(int $id): bool;
    public function findAll(int $limit = 50, int $offset = 0): array;
    public function updateLastLogin(int $userId): bool;
}

interface PhotoRepositoryInterface {
    public function create(array $data): Photo;
    public function findById(int $id): ?Photo;
    public function update(Photo $photo): bool;
    public function delete(int $id): bool;
    public function findByUserId(int $userId, int $limit = 50, int $offset = 0): array;
    public function findByAlbumId(int $albumId): array;
    public function findPublished(int $limit = 50, int $offset = 0): array;
    public function incrementViewCount(int $photoId): bool;
    public function findByState(string $state, int $limit = 50, int $offset = 0): array;
}

interface AlbumRepositoryInterface {
    public function create(array $data): Album;
    public function findById(int $id): ?Album;
    public function update(Album $album): bool;
    public function delete(int $id): bool;
    public function findByPublisherId(int $publisherId): array;
    public function findPublic(int $limit = 50, int $offset = 0): array;
    public function updatePhotoCount(int $albumId): bool;
}

interface TagRepositoryInterface {
    public function create(string $slug): Tag;
    public function findById(int $id): ?Tag;
    public function findBySlug(string $slug): ?Tag;
    public function findOrCreate(string $slug): Tag;
    public function update(Tag $tag): bool;
    public function delete(int $id): bool;
    public function findAll(): array;
    public function findPopular(int $limit = 20): array;
}

interface CommentRepositoryInterface {
    public function create(array $data): Comment;
    public function findById(int $id): ?Comment;
    public function update(Comment $comment): bool;
    public function delete(int $id): bool;
    public function findByPhotoId(int $photoId): array;
    public function findByUserId(int $userId): array;
}

interface LikeRepositoryInterface {
    public function create(int $userId, int $photoId): bool;
    public function delete(int $userId, int $photoId): bool;
    public function exists(int $userId, int $photoId): bool;
    public function findByPhotoId(int $photoId): array;
    public function findByUserId(int $userId): array;
    public function countByPhotoId(int $photoId): int;
}

interface PhotoTagRepositoryInterface {
    public function addTag(int $photoId, int $tagId): bool;
    public function removeTag(int $photoId, int $tagId): bool;
    public function findTagsByPhotoId(int $photoId): array;
    public function findPhotosByTagId(int $tagId): array;
}