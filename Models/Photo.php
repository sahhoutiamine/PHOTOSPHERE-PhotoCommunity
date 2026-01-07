<?php

declare(strict_types=1);

require_once __DIR__ . '/../Interfaces/Taggable.php';
require_once __DIR__ . '/../Interfaces/Commentable.php';
require_once __DIR__ . '/../Interfaces/Likeable.php';
require_once __DIR__ . '/../Traits/TaggableTrait.php';
require_once __DIR__ . '/../Traits/TimestampableTrait.php';

class Photo implements Taggable, Commentable, Likeable
{
    use TaggableTrait;
    use TimestampableTrait;
    
    private int $id;
    private ?string $title;
    private ?string $description;
    private string $imageLink;
    private ?int $fileSize;
    private ?string $dimensions;
    private ?string $state;
    private int $viewCount;
    private ?string $publishedAt;
    private int $userId;
    private ?int $albumId;
    private int $likeCount;
    private int $commentCount;
    private bool $isPublic;
    private array $likedBy = [];
    private array $comments = [];
    
    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->title = $data['title'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->imageLink = $data['imageLink'] ?? '';
        $this->fileSize = $data['fileSize'] ?? null;
        $this->dimensions = $data['dimensions'] ?? null;
        $this->state = $data['state'] ?? 'draft';
        $this->viewCount = $data['viewCount'] ?? 0;
        $this->publishedAt = $data['publishedAt'] ?? null;
        $this->userId = $data['userId'] ?? 0;
        $this->albumId = $data['albumId'] ?? null;
        $this->likeCount = $data['likeCount'] ?? 0;
        $this->commentCount = $data['commentCount'] ?? 0;
        $this->isPublic = $data['isPublic'] ?? true;
        
        if (isset($data['createdAt'])) {
            $this->setCreatedAt($data['createdAt']);
        }
        if (isset($data['updatedAt'])) {
            $this->setUpdatedAt($data['updatedAt']);
        }
        
        if ($this->id === 0) {
            $this->initializeTimestamps();
        }
    }
    
    public function getId(): int
    {
        return $this->id;
    }
    
    public function getTitle(): ?string
    {
        return $this->title;
    }
    
    public function getDescription(): ?string
    {
        return $this->description;
    }
    
    public function getImageLink(): string
    {
        return $this->imageLink;
    }
    
    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }
    
    public function getDimensions(): ?string
    {
        return $this->dimensions;
    }
    
    public function getState(): ?string
    {
        return $this->state;
    }
    
    public function getViewCount(): int
    {
        return $this->viewCount;
    }
    
    public function getPublishedAt(): ?string
    {
        return $this->publishedAt;
    }
    
    public function getUserId(): int
    {
        return $this->userId;
    }
    
    public function getAlbumId(): ?int
    {
        return $this->albumId;
    }
    
    public function isPublic(): bool
    {
        return $this->isPublic;
    }
    
    public function setTitle(?string $title): void
    {
        $this->title = $title;
        $this->updateTimestamps();
    }
    
    public function setDescription(?string $description): void
    {
        $this->description = $description;
        $this->updateTimestamps();
    }
    
    public function setState(string $state): void
    {
        $this->state = $state;
        $this->updateTimestamps();
    }
    
    public function setAlbumId(?int $albumId): void
    {
        $this->albumId = $albumId;
        $this->updateTimestamps();
    }
    
    public function setPublic(bool $isPublic): void
    {
        $this->isPublic = $isPublic;
        $this->updateTimestamps();
    }
    
    public function incrementViewCount(): void
    {
        $this->viewCount++;
    }
    
    public function publish(): void
    {
        $this->state = 'published';
        $this->publishedAt = date('Y-m-d H:i:s');
        $this->updateTimestamps();
    }
    
    public function archive(): void
    {
        $this->state = 'archived';
        $this->updateTimestamps();
    }
    
    public function isDraft(): bool
    {
        return $this->state === 'draft';
    }
    
    public function isPublished(): bool
    {
        return $this->state === 'published';
    }
    
    public function addComment(string $content, int $userId): int
    {
        $commentId = count($this->comments) + 1;
        $this->comments[$commentId] = [
            'id' => $commentId,
            'content' => $content,
            'userId' => $userId,
            'createdAt' => date('Y-m-d H:i:s')
        ];
        $this->commentCount++;
        $this->updateTimestamps();
        return $commentId;
    }
    
    public function removeComment(int $commentId): bool
    {
        if (isset($this->comments[$commentId])) {
            unset($this->comments[$commentId]);
            $this->commentCount = max(0, $this->commentCount - 1);
            $this->updateTimestamps();
            return true;
        }
        return false;
    }
    
    public function getComments(): array
    {
        return array_values($this->comments);
    }
    
    public function getCommentCount(): int
    {
        return $this->commentCount;
    }
    
    public function addLike(int $userId): bool
    {
        if (!in_array($userId, $this->likedBy, true)) {
            $this->likedBy[] = $userId;
            $this->likeCount++;
            $this->updateTimestamps();
            return true;
        }
        return false;
    }
    
    public function removeLike(int $userId): bool
    {
        $key = array_search($userId, $this->likedBy, true);
        if ($key !== false) {
            unset($this->likedBy[$key]);
            $this->likedBy = array_values($this->likedBy);
            $this->likeCount = max(0, $this->likeCount - 1);
            $this->updateTimestamps();
            return true;
        }
        return false;
    }
    
    public function isLikedBy(int $userId): bool
    {
        return in_array($userId, $this->likedBy, true);
    }
    
    public function getLikeCount(): int
    {
        return $this->likeCount;
    }
    
    public function getLikedBy(): array
    {
        return $this->likedBy;
    }
    
    protected function loadTagsFromDatabase(): void
    {
        // To be implemented with database connection
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'imageLink' => $this->imageLink,
            'fileSize' => $this->fileSize,
            'dimensions' => $this->dimensions,
            'state' => $this->state,
            'viewCount' => $this->viewCount,
            'publishedAt' => $this->publishedAt,
            'createdAt' => $this->getCreatedAt('Y-m-d H:i:s'),
            'updatedAt' => $this->getUpdatedAt('Y-m-d H:i:s'),
            'userId' => $this->userId,
            'albumId' => $this->albumId,
            'likeCount' => $this->likeCount,
            'commentCount' => $this->commentCount,
            'isPublic' => $this->isPublic,
            'tags' => $this->getTags()
        ];
    }
}