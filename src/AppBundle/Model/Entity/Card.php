<?php

namespace AppBundle\Model\Entity;

use AppBundle\Exception\MoneyException;
use Doctrine\Common\Collections\ArrayCollection;
use Money\Currency;
use Money\Money;
use Ramsey\Uuid\Uuid;

class Card implements \JsonSerializable
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $userId;

    /**
     * @var Money
     */
    private $balance;

    /**
     * @var Money
     */
    private $blocked;

    /**
     * @var ArrayCollection
     */
    private $transactions;

    /**
     * @var ArrayCollection
     */
    private $authorisedRequests;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @param $userId
     */
    public function __construct($userId)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->userId  = $userId;
        $this->balance = new Money('0', new Currency('GBP'));
        $this->blocked = new Money('0', new Currency('GBP'));
        $this->transactions = new ArrayCollection();
        $this->authorisedRequests = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    /**
     * @param Money $amount
     *
     * @throws MoneyException
     */
    public function topUp(Money $amount)
    {
        if ($amount->isNegative() || $amount->isZero()) {
            throw new MoneyException('Amount to top up must be greater than 0');
        }

        $this->balance = $this->balance->add($amount);

        $this->transactions->add(Transaction::credit($amount, 'Top up!'));
    }

    /**
     * @param Money $amount
     * @param $merchantId
     *
     * @return AuthorisedRequest
     *
     * @throws MoneyException
     */
    public function charge(Money $amount, $merchantId)
    {
        if ($amount->isNegative() || $amount->isZero()) {
            throw new MoneyException('Amount to charge must be greater than 0');
        }

        $authorisedRequest = new AuthorisedRequest($this, $merchantId, $amount);

        $this->balance = $this->balance->subtract($amount);

        if ($this->balance->isNegative()) {
            throw new MoneyException('There is not enough money in the account.');
        }

        $this->blocked = $this->blocked->add($amount);

        $this->transactions->add(Transaction::debit($amount, sprintf('%s charge', $merchantId)));
        $this->authorisedRequests->add($authorisedRequest);

        return $authorisedRequest;
    }

    /**
     * @param Money $amount
     *
     * @throws MoneyException
     */
    public function refund(Money $amount)
    {
        if ($amount->isNegative() || $amount->isZero()) {
            throw new MoneyException('Amount to refund must be greater than 0');
        }

        $this->balance = $this->balance->add($amount);

        $this->transactions->add(Transaction::credit($amount, 'Refund!'));
    }

    /**
     * @param Money $amount
     *
     * @throws MoneyException
     */
    public function reverse(Money $amount)
    {
        if ($amount->isNegative() || $amount->isZero()) {
            throw new MoneyException('Amount to reverse must be greater than 0');
        }

        $this->blocked = $this->blocked->subtract($amount);
        $this->balance = $this->balance->add($amount);

        if ($this->blocked->isNegative()) {
            throw new MoneyException('Can not reverse more than the blocked amount');
        }

        $this->transactions->add(Transaction::credit($amount, 'Reversed charge'));
    }

    /**
     * @param Money $amount
     *
     * @throws MoneyException
     */
    public function capture(Money $amount)
    {
        if ($amount->isNegative() || $amount->isZero()) {
            throw new MoneyException('Amount to reverse must be greater than 0');
        }

        $this->blocked = $this->blocked->subtract($amount);

        if ($this->blocked->isNegative()) {
            throw new MoneyException('Can not capture more than the blocked amount');
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Money
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @return Money
     */
    public function getBlockedBalance()
    {
        return $this->blocked;
    }

    /**
     * @return array | Transaction[]
     */
    public function getTransactions()
    {
        return $this->transactions->toArray();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->userId,
            'balance'    => $this->balance,
            'blocked'    => $this->blocked,
            'created_at' => $this->createdAt->format('c')
        ];
    }
}
