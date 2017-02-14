<?php

namespace AppBundle\Repository;

use AppBundle\Model\Entity\AuthorisedRequest;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class DoctrineAuthorisedRequestRepository extends EntityRepository
{
    /**
     * @param AuthorisedRequest $authorisedRequest
     */
    public function save(AuthorisedRequest $authorisedRequest)
    {
        $this->getEntityManager()->persist($authorisedRequest);
        $this->getEntityManager()->flush();
    }

    /**
     * @param $id
     *
     * @return null| AuthorisedRequest
     */
    public function findAuthorisationById($id)
    {
        $authorisation = $this->find($id);

        if (!$authorisation instanceof AuthorisedRequest) {
            throw new ResourceNotFoundException(
                sprintf('Authorisation with id [%s] not found', $id)
            );
        }

        return $authorisation;
    }
}
