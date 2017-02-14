<?php

namespace AppBundle\Model\Entity;

use Money\Money;
use Ramsey\Uuid\Uuid;

class Transaction implements \JsonSerializable
{
    const TYPE_CREDIT = 'credit';
    const TYPE_DEBIT = 'debit';

    private $id;
    private $type;
    private $amount;
    private $description;
    private $createdAt;

    private function __construct($type, Money $amount, $description)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->type = $type;
        $this->amount = $amount;
        $this->description = $description;
        $this->createdAt = new \DateTime();
    }

    public static function debit(Money $amount, $description)
    {
        return new self(self::TYPE_DEBIT, $amount, $description);
    }

    public static function credit(Money $amount, $description)
    {
        return new self(self::TYPE_CREDIT, $amount, $description);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id'          => $this->id,
            'type'        => $this->type,
            'amount'      => $this->amount,
            'description' => $this->description,
            'created_at'  => $this->createdAt->format('c')
        ];
    }
}
