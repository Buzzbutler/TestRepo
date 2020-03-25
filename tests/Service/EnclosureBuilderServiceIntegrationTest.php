<?php

namespace App\Tests\Service;


use App\Entity\Dinosaur;
use App\Entity\Enclosure;
use App\Entity\Security;
use App\Factory\DinosaurFactory;
use App\Service\EnclosureBuilderService;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EnclosureBuilderServiceIntegrationTest extends KernelTestCase
{

    public function setUp(): void
    {
        self::bootKernel();

        $this->truncateEntities([
            Enclosure::class,
            Security::class,
            Dinosaur::class,
        ]);
    }

    /**
     * @group failing
     */
    public function testItBuildsEnclosureWithDefaultSpecifications()
    {

//        $enclosureBuilderService = self::$kernel->getContainer()
//            ->get('test.' . EnclosureBuilderService::class);

        $dinoFactory = $this->createMock(DinosaurFactory::class);
        $dinoFactory->expects($this->any())
            ->method('growFromSpecification')
            ->willReturnCallback(function($spec) {
                return new Dinosaur();
            });

        $enclosureBuilderService = new EnclosureBuilderService(
            $this->getEntityManager(),
            $dinoFactory
        );

        $enclosureBuilderService->buildEnclosure();

        $em = $this->getEntityManager();

        $count = (int)$em->getRepository(Security::class)
            ->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $this->assertSame(1, $count, 'Amount of security systems is not the same');

        $count = (int)$em->getRepository(Dinosaur::class)
            ->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $this->assertSame(3, $count, 'Amount of dinosaurs is not the same');
    }

    private function truncateEntities(array $entities)
    {

        $purger = new ORMPurger($this->getEntityManager());
        $purger->purge();

//        $connection = $this->getEntityManager()->getConnection();
//        $databasePlatform = $connection->getDatabasePlatform();
//        if ($databasePlatform->supportsForeignKeyConstraints()) {
//            $connection->query('SET FOREIGN_KEY_CHECKS=0');
//        }
//        foreach ($entities as $entity) {
//            $query = $databasePlatform->getTruncateTableSQL(
//                $this->getEntityManager()->getClassMetadata($entity)->getTableName()
//            );
//            $connection->executeUpdate($query);
//        }
//        if ($databasePlatform->supportsForeignKeyConstraints()) {
//            $connection->query('SET FOREIGN_KEY_CHECKS=1');
//        }
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager()
    {
        return self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }
}