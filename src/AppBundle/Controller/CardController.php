<?php

namespace AppBundle\Controller;

use AppBundle\Model\Entity\Card;
use Money\Money;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/cards")
 */
class CardController extends Controller
{
    /**
     * @Route("", name="create_card")
     * @Method({"POST"})
     *
     * @return JsonResponse
     */
    public function createCard()
    {
        $userId = $this->get('app.request_content')->get('user_id');

        if (!$userId) {
            throw new \InvalidArgumentException('[user_id] should be provided');
        }

        $card = new Card($userId);

        $this->get('app.repository.card')->save($card);

        return JsonResponse::create($card, 201);
    }

    /**
     * @Route("/{cardId}", name="check_card_balance")
     * @Method({"GET"})
     *
     * @param $cardId
     *
     * @return JsonResponse
     */
    public function checkBalance($cardId)
    {
        $card = $this->get('app.repository.card')->findCardId($cardId);

        return JsonResponse::create($card, 200);
    }

    /**
     * @Route("/{cardId}/top-ups", name="top_up_card")
     * @Method({"POST"})
     *
     * @param $cardId
     *
     * @return JsonResponse
     */
    public function topUpAction($cardId)
    {
        $amount = $this->get('app.request_content')->get('amount');

        if (!$amount) {
            throw new \InvalidArgumentException('[amount] should be provided');
        }

        $card = $this->get('app.repository.card')->findCardId($cardId);

        $card->topUp(Money::GBP($amount));

        $this->get('app.repository.card')->save($card);

        return JsonResponse::create($card, 200);
    }

    /**
     * @Route("/{cardId}/charges", name="charge_card")
     * @Method({"POST"})
     *
     * @param $cardId
     *
     * @return JsonResponse
     */
    public function chargeAction($cardId)
    {
        $amount = $this->get('app.request_content')->get('amount');
        $merchantId = $this->get('app.request_content')->get('merchant_id');

        if (!$amount) {
            throw new \InvalidArgumentException('[amount] should be provided');
        }

        if (!$merchantId) {
            throw new \InvalidArgumentException('[merchant_id] should be provided');
        }

        $card = $this->get('app.repository.card')->findCardId($cardId);

        $authorisation = $card->charge(Money::GBP($amount), $merchantId);

        $this->get('app.repository.card')->save($card);

        return JsonResponse::create(['authorisation_id' => $authorisation->getId()], 200);
    }

    /**
     * @Route("/{cardId}/transactions", name="check_transactions")
     * @Method({"GET"})
     *
     * @param $cardId
     *
     * @return JsonResponse
     */
    public function checkTransactions($cardId)
    {
        $card = $this->get('app.repository.card')->findCardId($cardId);

        return JsonResponse::create($card->getTransactions(), 200);
    }
}
