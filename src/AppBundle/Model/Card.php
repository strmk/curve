<?php

namespace AppBundle\Model;

class Card
{
    private $userId;
    private $balance;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }
}
