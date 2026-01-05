<?php
require_once __DIR__ . '/User.php';

class ProUser extends User {
    public function canUploadPhoto(): bool {
        return true; 
    }
    
    public function canCreatePrivateAlbum(): bool {
        return true;
    }
    
    public function getUploadLimit(): ?int {
        return null; 
    }
    
    public function hasActiveSubscription(): bool {
        if (!$this->subscriptionEnd) {
            return false;
        }
        return strtotime($this->subscriptionEnd) > time();
    }
}