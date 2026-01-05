<?php
// Administrator.php
namespace PhotoSphere\Models;

class Administrator extends User
{
    public function __construct(
        string $username,
        string $email,
        string $password
    ) {
        parent::__construct($username, $email, $password);
        $this->level = 'administrator';
    }
    
    public function canCreatePrivateAlbum(): bool
    {
        return true;
    }
    
    public function getMonthlyUploadLimit(): ?int
    {
        return null; 
    }
    
    public function canManageUser(User $user): bool
    {
        
        return $user->getId() !== $this->id;
    }
    
    public function canModifySystemSettings(): bool
    {
        return $this->isSuper();
    }
}