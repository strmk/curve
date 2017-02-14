<?php

namespace Tests\Functional;

use AppBundle\Model\Entity\Card;
use Money\Money;

/**
 * @group Functional
 */
class CardControllerTest extends FunctionalTestBase
{
    /**
     * @var Card
     */
    private $card;

    public function setUp()
    {
        parent::setUp();

        $this->card = new Card('uuid');
        $this->entityManager->persist($this->card);
        $this->entityManager->flush();
    }

    /**
     * @test
     */
    public function canCreateCard()
    {
        $response = $this->callApi('POST', '/api/cards', ['user_id' => '1234']);

        $this->assertEquals(201, $response->getStatusCode());

        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertNotNull($decodedResponse['id']);
        $card = $this->getEntityManager()->getRepository(Card::class)->find($decodedResponse['id']);
        $this->assertInstanceOf(Card::class, $card);
    }

    /**
     * @test
     */
    public function canViewCardBalanceAndBlockedAmounts()
    {
        $response = $this->callApi('GET', sprintf('/api/cards/%s', $this->card->getId()));

        $this->assertEquals(200, $response->getStatusCode());

        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertSame($this->card->getId(), $decodedResponse['id']);
        $this->assertNotNull($decodedResponse['balance']['amount']);
        $this->assertNotNull($decodedResponse['blocked']['amount']);
    }

    /**
     * @test
     */
    public function canTopUpCard()
    {
        $response = $this->callApi('POST', sprintf('/api/cards/%s/top-ups', $this->card->getId()), ['amount' => 100]);

        $this->assertEquals(200, $response->getStatusCode());

        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertEquals(100, $decodedResponse['balance']['amount']);
    }

    /**
     * @test
     */
    public function canChargeCard()
    {
        $this->card->topUp(Money::GBP(100));
        $this->entityManager->persist($this->card);
        $this->entityManager->flush();

        $response = $this->callApi(
            'POST',
            sprintf('/api/cards/%s/charges', $this->card->getId()),
            [
                'amount' => 100,
                'merchant_id' => 'merchant uuid'
            ]
        );

        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());

        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertNotNull($decodedResponse['authorisation_id']);
    }

    /**
     * @test
     */
    public function canViewCardTransactions()
    {
        $this->card->topUp(Money::GBP(100));
        $this->entityManager->persist($this->card);
        $this->entityManager->flush();

        $response = $this->callApi(
            'GET',
            sprintf('/api/cards/%s/transactions', $this->card->getId()),
            [
                'amount' => 100,
                'merchant_id' => 'merchant uuid'
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $decodedResponse = json_decode($response->getContent(), true);
        $this->assertCount(1, $decodedResponse);
        $this->assertContains('Top up', $decodedResponse[0]['description']);
    }
}
