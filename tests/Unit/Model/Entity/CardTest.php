<?php

namespace Tests\Unit\Model\Entity;

use AppBundle\Model\Entity\Card;
use Money\Money;

/**
 * @group Unit
 */
class CardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canBeToppedUp()
    {
        $card = new Card('user uuid');
        $card->topUp(Money::GBP(10000));

        $this->assertSame('10000', $card->getBalance()->getAmount());
    }

    /**
     * @test
     * @dataProvider invalidAmountsDataProvider
     * @expectedException \AppBundle\Exception\MoneyException
     * @expectedExceptionMessage must be greater than
     */
    public function canNotTopUpNegativeAmounts($amount)
    {
        $card = new Card('user uuid');
        $card->topUp(Money::GBP($amount));
    }

    public function invalidAmountsDataProvider()
    {
        return [
            [-1000],
            [0]
        ];
    }

    /**
     * @test
     */
    public function canBeCharged()
    {
        $card = new Card('user uuid');
        $card->topUp(Money::GBP(10000));
        $card->charge(Money::GBP(7500), 'merchant uuid');

        $this->assertSame('2500', $card->getBalance()->getAmount());
        $this->assertSame('7500', $card->getBlockedBalance()->getAmount());
    }

    /**
     * @test
     * @expectedException \AppBundle\Exception\MoneyException
     * @expectedExceptionMessage not enough money
     */
    public function canNotBeChargedIfThereIsNotEnoughMoney()
    {
        $card = new Card('user uuid');
        $card->topUp(Money::GBP(10000));
        $card->charge(Money::GBP(10001), 'merchant uuid');
    }

    /**
     * @test
     * @dataProvider invalidAmountsDataProvider
     * @expectedException \AppBundle\Exception\MoneyException
     * @expectedExceptionMessage must be greater than
     */
    public function canNotBeCharged0OrLess($amount)
    {
        $card = new Card('user uuid');
        $card->topUp(Money::GBP(10000));
        $card->charge(Money::GBP($amount), 'merchant uuid');
    }
}
