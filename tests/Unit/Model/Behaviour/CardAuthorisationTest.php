<?php

namespace Tests\Unit\Model\Behaviour;

use AppBundle\Exception\CaptureException;
use AppBundle\Model\Entity\AuthorisedRequest;
use AppBundle\Model\Entity\Card;
use Money\Money;

/**
 * @group Unit
 */
class CardAuthorisationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Card
     */
    private $card;

    public function setUp()
    {
        $this->card = new Card('user uuid');
        $this->card->topUp(Money::GBP(10000));
    }

    /**
     * @test
     */
    public function canSpendMoneyInCoffee()
    {
        $this->assertSame('10000', $this->card->getBalance()->getAmount());
        $this->assertSame('0', $this->card->getBlockedBalance()->getAmount());

        $authorisedRequest = $this->card->charge(Money::GBP('5000'), 'coffee shop uuid');

        $this->assertInstanceOf(AuthorisedRequest::class, $authorisedRequest);
        $this->assertSame('5000', $this->card->getBalance()->getAmount());
        $this->assertSame('5000', $this->card->getBlockedBalance()->getAmount());
        $this->assertSame('5000', $authorisedRequest->getAuthorisedAmount()->getAmount());

        $authorisedRequest->reverse(Money::GBP('2500'));
        $this->assertSame('7500', $this->card->getBalance()->getAmount());
        $this->assertSame('2500', $this->card->getBlockedBalance()->getAmount());
        $this->assertSame('2500', $authorisedRequest->getAuthorisedAmount()->getAmount());

        $authorisedRequest->capture(Money::GBP('2500'));
        $this->assertSame('7500', $this->card->getBalance()->getAmount());
        $this->assertSame('0', $this->card->getBlockedBalance()->getAmount());
        $this->assertSame('0', $authorisedRequest->getAuthorisedAmount()->getAmount());

        $authorisedRequest->refund(Money::GBP('2500'));
        $this->assertSame('10000', $this->card->getBalance()->getAmount());
        $this->assertSame('0', $this->card->getBlockedBalance()->getAmount());
        $this->assertSame('0', $authorisedRequest->getAuthorisedAmount()->getAmount());

        try {
            $authorisedRequest->reverse(Money::GBP('2500'));
            $this->fail('Should not be able to reverse money');
        } catch (CaptureException $e) {
            $this->assertContains('revert more money than the authorised', $e->getMessage());
        }

        try {
            $authorisedRequest->refund(Money::GBP('2500'));
            $this->fail('Should not be able to refund more money');
        } catch (CaptureException $e) {
            $this->assertContains('refund more money than what has', $e->getMessage());
        }

        try {
            $authorisedRequest->capture(Money::GBP('2500'));
            $this->fail('Should not be able to capture more money');
        } catch (CaptureException $e) {
            $this->assertContains('capture more money than the transacted amount', $e->getMessage());
        }
    }
}
