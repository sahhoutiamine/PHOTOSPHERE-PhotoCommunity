<?php
// Moderator.php

class Moderator extends User
{
    private string $moderatorLevel; 
    
    public function __construct(
        string $username,
        string $email,
        string $password,
        string $moderatorLevel = 'junior'
    ) {
        parent::__construct($username, $email, $password);
        $this->setModeratorLevel($moderatorLevel);
        $this->level = 'moderator';
    }
    
    public function canCreatePrivateAlbum(): bool
    {
        return true;
    }
    
    public function getMonthlyUploadLimit(): ?int
    {
        return null; 
    }
    
    
    
    public function getModeratorLevel(): string
    {
        return $this->moderatorLevel;
    }
    
    public function canDeleteComment(Comment $comment, User $photoOwner): bool
    {
        return true;
    }
    
    public function canSuspendUser(User $user): bool
    {
        if ($this->moderatorLevel === 'junior') {
            return $user instanceof BasicUser;
        }
        
        if ($this->moderatorLevel === 'senior' || $this->moderatorLevel === 'lead') {
            return $user instanceof BasicUser || $user instanceof ProUser;
        }
        
        return false;
    }
    
    
    
}