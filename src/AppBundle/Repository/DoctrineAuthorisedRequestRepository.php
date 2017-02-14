<?php

namespace AppBundle\Repository;

use AppBundle\Model\Entity\AuthorisedRequest;
use Doctrine\ORM\EntityRepository;

class DoctrineAuthorisedRequestRepository extends EntityRepository
{
    public function save(AuthorisedRequest $authorisedRequest)
    {
        $this->getEntityManager()->persist($authorisedRequest);
        $this->getEntityManager()->flush();
    }
}
