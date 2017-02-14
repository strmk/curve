<?php

namespace AppBundle\Repository;

use AppBundle\Model\Entity\Card;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class DoctrineCardRepository extends EntityRepository
{
    /**
     * @param Card $card
     */
    public function save(Card $card)
    {
        $this->getEntityManager()->persist($card);
        $this->getEntityManager()->flush();
    }

    /**
     * @param $id
     *
     * @throws ResourceNotFoundException
     *
     * @return Card $card
     */
    public function findCardId($id)
    {
        $card = $this->find($id);

        if(!$card instanceof Card) {
            throw new ResourceNotFoundException(
                sprintf('Card with id [%s] not found', $id)
            );
        }

        return $card;
    }
}
