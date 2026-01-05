<?php
require_once __DIR__ . '/User.php';

class Administrator extends User {
    public function canUploadPhoto(): bool {
        return true;
    }
    
    public function canCreatePrivateAlbum(): bool {
        return true;
    }
    
    public function getUploadLimit(): ?int {
        return null;
    }
    
    public function hasFullAccess(): bool {
        return true;
    }
}