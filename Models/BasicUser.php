<?php
// BasicUser.php
namespace PhotoSphere\Models;

class BasicUser extends User
{
    private const MONTHLY_UPLOAD_LIMIT = 10;
    private int $currentMonthUploadCount = 0;
    private \DateTime $currentMonthReset;
    
    public function __construct(
        string $username,
        string $email,
        string $password
    ) {
        parent::__construct($username, $email, $password);
        $this->currentMonthReset = new \DateTime('first day of this month');
        $this->level = 'basic';
    }
    
    public function canCreatePrivateAlbum(): bool
    {
        return false;
    }
    
    public function getMonthlyUploadLimit(): ?int
    {
        return self::MONTHLY_UPLOAD_LIMIT;
    }
    
    public function getCurrentMonthUploadCount(): int
    {
        $this->checkMonthReset();
        return $this->currentMonthUploadCount;
    }
    
    public function canUpload(): bool
    {
        $this->checkMonthReset();
        return $this->currentMonthUploadCount < self::MONTHLY_UPLOAD_LIMIT;
    }
    
    public function incrementUpload(): void
    {
        $this->checkMonthReset();
        if (!$this->canUpload()) {
            throw new \Exception("Monthly upload limit reached");
        }
        $this->currentMonthUploadCount++;
        $this->incrementUploadCount();
    }
    
    private function checkMonthReset(): void
    {
        $now = new \DateTime();
        if ($now->format('Y-m') > $this->currentMonthReset->format('Y-m')) {
            $this->currentMonthUploadCount = 0;
            $this->currentMonthReset = new \DateTime('first day of this month');
        }
    }
}