<?php
// ProUser.php

class ProUser extends User
{
    
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
        return null; 
    }
    
    public function setSubscription(\DateTime $start, \DateTime $end): void
    {
        $this->subscriptionStart = $start;
        $this->subscriptionEnd = $end;
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