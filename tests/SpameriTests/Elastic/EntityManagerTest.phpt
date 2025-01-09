<?php declare(strict_types = 1);

namespace SpameriTests\Elastic;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
class EntityManagerTest extends \SpameriTests\Elastic\AbstractTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        /** @var \Spameri\Elastic\Model\Indices\Create $create */
        $create = $this->container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
        $create->execute(\SpameriTests\Elastic\Config::INDEX_TITLE, []);
        $create->execute(\SpameriTests\Elastic\Config::INDEX_IMAGE, []);
    }


    public function testProcess(): void
    {
        /** @var \Spameri\Elastic\EntityManager $entityManager */
        $entityManager = $this->container->getByType(\Spameri\Elastic\EntityManager::class);

        $title = new \SpameriTests\Elastic\Data\Entity\Title(
            new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
            null,
        );
        $image = new \SpameriTests\Elastic\Data\Entity\Image(
            new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
            null
        );

        $image->title = $title;

        $entityManager->persist($image);

        $image->isGuess = true;
        $title->backdrop = $image;

        $entityManager->persist($image);

        $titles = $entityManager->findAll($title::class);

        \Tester\Assert::true(count($titles) === 1);
    }


    protected function tearDown()
    {
        /** @var \Spameri\Elastic\Model\Indices\Delete $delete */
        $delete = $this->container->getByType(\Spameri\Elastic\Model\Indices\Delete::class);
        $delete->execute(\SpameriTests\Elastic\Config::INDEX_TITLE);
        $delete->execute(\SpameriTests\Elastic\Config::INDEX_IMAGE);
    }

}

(new EntityManagerTest())->run();
