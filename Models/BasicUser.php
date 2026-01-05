<?php
require_once __DIR__ . '/User.php';

class BasicUser extends User {
    private const MONTHLY_UPLOAD_LIMIT = 10;
    
    public function canUploadPhoto(): bool {
        return $this->uploadCount < self::MONTHLY_UPLOAD_LIMIT;
    }
    
    public function canCreatePrivateAlbum(): bool {
        return false;
    }
    
    public function getUploadLimit(): ?int {
        return self::MONTHLY_UPLOAD_LIMIT;
    }
    
    public function getRemainingUploads(): int {
        return max(0, self::MONTHLY_UPLOAD_LIMIT - $this->uploadCount);
    }
}