<?php

namespace AppBundle\Controller;

use Money\Money;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/merchants")
 */
class MerchantController extends Controller
{
    /**
     * @Route("/captures/{authorisationId}", name="capture_amount")
     * @Method({"POST"})
     *
     * @param $authorisationId
     *
     * @return JsonResponse
     */
    public function captureAction($authorisationId)
    {
        $amount = $this->get('app.request_content')->get('amount');

        $authorisation = $this->get('app.repository.authorised_request')->findAuthorisationById($authorisationId);

        $authorisation->capture(Money::GBP($amount));

        $this->get('app.repository.authorised_request')->save($authorisation);

        return JsonResponse::create();
    }

    /**
     * @Route("/refunds/{authorisationId}", name="refund_amount")
     * @Method({"POST"})
     *
     * @param $authorisationId
     *
     * @return JsonResponse
     */
    public function refundAction($authorisationId)
    {
        $amount = $this->get('app.request_content')->get('amount');

        $authorisation = $this->get('app.repository.authorised_request')->findAuthorisationById($authorisationId);

        $authorisation->refund(Money::GBP($amount));

        $this->get('app.repository.authorised_request')->save($authorisation);

        return JsonResponse::create();
    }

    /**
     * @Route("/reverses/{authorisationId}", name="reverse_amount")
     * @Method({"POST"})
     *
     * @param $authorisationId
     *
     * @return JsonResponse
     */
    public function reverseAction($authorisationId)
    {
        $amount = $this->get('app.request_content')->get('amount');

        $authorisation = $this->get('app.repository.authorised_request')->findAuthorisationById($authorisationId);

        $authorisation->reverse(Money::GBP($amount));

        $this->get('app.repository.authorised_request')->save($authorisation);

        return JsonResponse::create();
    }
}
