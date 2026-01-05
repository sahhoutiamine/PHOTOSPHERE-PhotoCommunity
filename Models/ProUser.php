<?php
// ProUser.php
namespace PhotoSphere\Models;

class ProUser extends User
{
    private ?\DateTime $subscriptionStart = null;
    private ?\DateTime $subscriptionEnd = null;
    
    public function __construct(
        string $username,
        string $email,
        string $password
    ) {
        parent::__construct($username, $email, $password);
        $this->level = 'pro';
    }
    
    public function canCreatePrivateAlbum(): bool
    {
        return $this->isSubscriptionActive();
    }
    
    public function getMonthlyUploadLimit(): ?int
    {
        return null; // Unlimited for Pro users
    }
    
    public function setSubscription(\DateTime $start, \DateTime $end): void
    {
        $this->subscriptionStart = $start;
        $this->subscriptionEnd = $end;
    }
    
    public function isSubscriptionActive(): bool
    {
        if ($this->subscriptionStart === null) {
            return false;
        }
        
        $now = new \DateTime();
        return $now >= $this->subscriptionStart && 
               ($this->subscriptionEnd === null || $now <= $this->subscriptionEnd);
    }
    
    public function getSubscriptionStart(): ?\DateTime
    {
        return $this->subscriptionStart;
    }
    
    public function getSubscriptionEnd(): ?\DateTime
    {
        return $this->subscriptionEnd;
    }
}