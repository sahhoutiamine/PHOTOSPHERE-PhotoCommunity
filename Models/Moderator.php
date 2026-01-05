<?php
require_once __DIR__ . '/User.php';

class Moderator extends User {
    public function canUploadPhoto(): bool {
        return true;
    }
    
    public function canCreatePrivateAlbum(): bool {
        return true;
    }
    
    public function getUploadLimit(): ?int {
        return null;
    }
    
    public function canModerateContent(): bool {
        return true;
    }
    
    public function canSuspendUser(): bool {
        return in_array($this->moderatorLevel, ['senior', 'lead']);
    }
}