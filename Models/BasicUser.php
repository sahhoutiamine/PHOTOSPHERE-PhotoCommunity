<?php
// BasicUser.php

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
    
   
}