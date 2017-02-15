<?php

namespace AppBundle\Model\Entity;

use AppBundle\Exception\CaptureException;
use Money\Money;
use Ramsey\Uuid\Uuid;

class AuthorisedRequest
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Card
     */
    private $card;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var Money
     */
    private $transactionAmount;

    /**
     * @var Money
     */
    private $capturedAmount;

    /**
     * @var Money
     */
    private $reversedAmount;

    /**
     * @var Money
     */
    private $refundAmount;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @param $card
     * @param $merchantId
     * @param Money $transactionAmount
     */
    public function __construct(Card $card, $merchantId, Money $transactionAmount)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->card = $card;
        $this->merchantId = $merchantId;
        $this->transactionAmount = $transactionAmount;
        $this->capturedAmount = new Money('0', $transactionAmount->getCurrency());
        $this->reversedAmount = new Money('0', $transactionAmount->getCurrency());
        $this->refundAmount = new Money('0', $transactionAmount->getCurrency());
        $this->createdAt = new \DateTime();
    }

    /**
     * @param Money $amount
     *
     * @throws CaptureException
     */
    public function capture(Money $amount)
    {
        $this->capturedAmount = $this->capturedAmount->add($amount);

        if ($this->getAuthorisedAmount()->isNegative()) {
            throw new CaptureException('Can not capture more money than the transacted amount.');
        }

        $this->card->capture($amount);
    }

    /**
     * @param Money $amount
     *
     * @throws CaptureException
     */
    public function reverse(Money $amount)
    {
        $this->reversedAmount = $this->reversedAmount->add($amount);

        if ($this->getAuthorisedAmount()->isNegative()) {
            throw new CaptureException('Can not revert more money than the authorised amount.');
        }

        $this->card->reverse($amount);
    }

    /**
     * @param Money $amount
     *
     * @throws CaptureException
     */
    public function refund(Money $amount)
    {
        $this->refundAmount = $this->refundAmount->add($amount);

        if ($this->refundAmount->greaterThan($this->getCapturedAmount())) {
            throw new CaptureException('Can not refund more money than what has been captured');
        }

        $this->card->refund($amount);
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
    public function getAuthorisedAmount()
    {
        return $this->transactionAmount->subtract($this->reversedAmount)->subtract($this->capturedAmount);
    }

    /**
     * @return Money
     */
    public function getCapturedAmount()
    {
        return $this->capturedAmount;
    }
}
