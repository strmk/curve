<?php

namespace AppBundle\Services;

use Symfony\Component\HttpFoundation\RequestStack;

class RequestContent
{
    /**
     * @var RequestStack
     */
    private $request;

    private $requestContent;

    /**
     * @param RequestStack $request
     */
    public function __construct(RequestStack $request)
    {
        $this->request = $request->getCurrentRequest();
        $this->requestContent = json_decode($this->request->getContent(), true);
    }

    /**
     * @param $var
     *
     * @return null
     */
    public function get($var)
    {
        if (isset($this->requestContent[$var])) {
            return $this->requestContent[$var];
        }

        return null;
    }
}
