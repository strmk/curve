<?php

namespace Functional;

use AppBundle\Model\Entity\AuthorisedRequest;
use AppBundle\Model\Entity\Card;
use Money\Money;
use Tests\Functional\FunctionalTestBase;

/**
 * @group Functional
 */
class MerchantControllerTest extends FunctionalTestBase
{
    /**
     * @var Card
     */
    private $card;

    /**
     * @var AuthorisedRequest
     */
    private $authorisation;

    public function setUp()
    {
        parent::setUp();

        $this->card = new Card('uuid');

        $this->card->topUp(Money::GBP(1000));
        $this->authorisation = $this->card->charge(Money::GBP(1000), 'merchant uuid');

        $this->entityManager->persist($this->card);
        $this->entityManager->flush();
    }

    /**
     * @test
     */
    public function canCaptureAnAmount()
    {
        $response = $this->callApi(
            'POST',
            sprintf('/api/merchants/captures/%s', $this->authorisation->getId()),
            ['amount' => 1000]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $card = $this->entityManager->getRepository(Card::class)->find($this->card->getId());
        $this->assertSame(0, $card->getBalance()->getAmount());
        $this->assertSame(0, $card->getBlockedBalance()->getAmount());
    }

    /**
     * @test
     */
    public function canReverseAnAmount()
    {
        $response = $this->callApi(
            'POST',
            sprintf('/api/merchants/reverses/%s', $this->authorisation->getId()),
            ['amount' => 1000]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $card = $this->entityManager->getRepository(Card::class)->find($this->card->getId());
        $this->assertSame(1000, $card->getBalance()->getAmount());
        $this->assertSame(0, $card->getBlockedBalance()->getAmount());
    }

    /**
     * @test
     */
    public function canRefundAnAmount()
    {
        $this->authorisation->capture(Money::GBP(1000));
        $this->entityManager->persist($this->authorisation);
        $this->entityManager->flush();

        $response = $this->callApi(
            'POST',
            sprintf('/api/merchants/refunds/%s', $this->authorisation->getId()),
            ['amount' => 1000]
        );

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());

        $card = $this->entityManager->getRepository(Card::class)->find($this->card->getId());
        $this->assertSame(1000, $card->getBalance()->getAmount());
        $this->assertSame(0, $card->getBlockedBalance()->getAmount());
    }
}
