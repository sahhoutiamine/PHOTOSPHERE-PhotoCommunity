<?php
// Moderator.php
namespace PhotoSphere\Models;

class Moderator extends User
{
    private string $moderatorLevel; 
    private array $moderationLog = [];
    
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
    
    public function setModeratorLevel(string $level): void
    {
        $allowedLevels = ['junior', 'senior', 'lead'];
        if (!in_array($level, $allowedLevels)) {
            throw new \InvalidArgumentException("Invalid moderator level");
        }
        $this->moderatorLevel = $level;
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
    
    public function logModeration(string $action, string $reason, int $targetUserId): void
    {
        $logEntry = [
            'action' => $action,
            'reason' => $reason,
            'target_user_id' => $targetUserId,
            'moderator_id' => $this->id,
            'timestamp' => new \DateTime(),
            'moderator_level' => $this->moderatorLevel
        ];
        
        $this->moderationLog[] = $logEntry;
    }
    
    public function getModerationLog(): array
    {
        return $this->moderationLog;
    }
}