<?php

namespace Unit\Model\Entity;

use AppBundle\Model\Entity\AuthorisedRequest;
use AppBundle\Model\Entity\Card;
use Money\Money;

/**
 * @group Unit
 */
class AuthorisedRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Card
     */
    private $card;

    /**
     * @var AuthorisedRequest
     */
    private $request;

    public function setUp()
    {
        $this->card = new Card('uuid');
        $this->card->topUp(Money::GBP(100000));
        $this->request = $this->card->charge(Money::GBP(100000), 'merchant uuid');
    }

    /**
     * @test
     */
    public function canGetAuthorisedAmount()
    {
        $this->card = new Card('uuid');
        $request = new AuthorisedRequest($this->card, 'merchant uuid', Money::GBP(5000));

        $this->assertSame('5000', $request->getAuthorisedAmount()->getAmount());
    }

    /**
     * @test
     */
    public function canCaptureTheFullAmount()
    {
        $this->request->capture(Money::GBP(100000));

        $this->assertSame('0', $this->request->getAuthorisedAmount()->getAmount());
    }

    /**
     * @test
     */
    public function canCaptureTheFullAmountInMultipleTimes()
    {
        $this->request->capture(Money::GBP(50000));
        $this->assertSame('50000', $this->request->getAuthorisedAmount()->getAmount());
        $this->request->capture(Money::GBP(50000));
        $this->assertSame('0', $this->request->getAuthorisedAmount()->getAmount());
    }

    /**
     * @test
     * @expectedException \AppBundle\Exception\CaptureException
     * @expectedExceptionMessage  more money than the transacted
     */
    public function canNotCaptureMoreMoneyThanTheOriginalAmount()
    {
        $this->request->capture(Money::GBP(100001));
    }

    /**
     * @test
     */
    public function canReverseAnAuthorisedAmount()
    {
        $this->request->reverse(Money::GBP(100000));
        $this->assertSame('0', $this->request->getAuthorisedAmount()->getAmount());
    }

    /**
     * @test
     */
    public function canPartiallyReverseAnAmountAndCaptureTheRest()
    {
        $this->request->reverse(Money::GBP(50000));
        $this->assertSame('50000', $this->request->getAuthorisedAmount()->getAmount());
        $this->request->capture(Money::GBP(50000));
        $this->assertSame('0', $this->request->getAuthorisedAmount()->getAmount());
    }

    /**
     * @test
     */
    public function canRefundCapturedMoney()
    {
        $this->request->capture(Money::GBP(50000));
        $this->assertSame('50000', $this->request->getAuthorisedAmount()->getAmount());
        $this->request->refund(Money::GBP(50000));
        $this->assertSame('50000', $this->request->getAuthorisedAmount()->getAmount());
    }

    /**
     * @test
     * @expectedException \AppBundle\Exception\CaptureException
     * @expectedExceptionMessage  capture more money than the
     */
    public function canNotCaptureMoreMoneyThanWhatShouldBeAvailable()
    {
        $this->request->reverse(Money::GBP(50000));
        $this->assertSame('50000', $this->request->getAuthorisedAmount()->getAmount());
        $this->request->capture(Money::GBP(50000));
        $this->assertSame('0', $this->request->getAuthorisedAmount()->getAmount());
        $this->request->refund(Money::GBP(50000));
        $this->assertSame('0', $this->request->getAuthorisedAmount()->getAmount());
        $this->request->capture(Money::GBP(1));
    }
}
