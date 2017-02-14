<?php

namespace Tests\Functional;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class FunctionalTestBase extends WebTestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function setUp()
    {
        parent::setUp();

        self::bootKernel();
        $this->container = self::$kernel->getContainer();
        $this->entityManager = $this->getService('doctrine.orm.default_entity_manager');
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();
    }

    /**
     * @param $method
     * @param $url
     *
     * @return Response
     */
    public function callApi($method, $url, $data = [])
    {
        $client = static::createClient();
        $client->request(
            $method,
            $url,
            [],
            [],
            [],
            json_encode($data)
        );

        return $client->getResponse();
    }

    /**
     * @param $service
     *
     * @return object
     */
    public function getService($service)
    {
        return $this->container->get($service);
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }
}
