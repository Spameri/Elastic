<?php declare(strict_types = 1);

define('TEMP_DIR', __DIR__ . '/tmp');

require_once __DIR__ . '/../vendor/autoload.php';

$config = new \Nette\Configurator();
$config->setTempDirectory(TEMP_DIR);
$config->addConfig(__DIR__ . '/SpameriTests/Elastic/Data/Config/Common.neon');
$container = $config->createContainer();

// Delete index
$client = $container->getByType(\Spameri\Elastic\ClientProvider::class)->client();
try { $client->indices()->delete(['index' => 'test_pagination*']); } catch (Exception $e) {}
usleep(100000);

// Create index
$create = $container->getByType(\Spameri\Elastic\Model\Indices\Create::class);
$create->execute('test_pagination', []);
usleep(100000);

// Insert 10 entities
$insert = $container->getByType(\Spameri\Elastic\Model\Insert::class);
$identityMap = $container->getByType(\Spameri\Elastic\Model\IdentityMap::class);
$ids = [];
for ($i = 0; $i < 10; $i++) {
    $entity = new \SpameriTests\Elastic\Data\Entity\Title(
        new \Spameri\Elastic\Entity\Property\EmptyElasticId(),
        null,
    );
    $isChanged = $identityMap->isChanged($entity);
    echo "Entity $i before insert - isChanged: " . ($isChanged ? 'true' : 'false') . ", id: '" . $entity->id()->value() . "'" . PHP_EOL;
    $ids[] = $insert->execute($entity, 'test_pagination', false);
    echo "Insert $i result: " . $entity->id()->value() . PHP_EOL;
    echo "Persisted map: " . print_r($identityMap->persisted, true);
}

echo 'Inserted IDs: ' . count($ids) . PHP_EOL;
echo 'Unique IDs: ' . count(array_unique($ids)) . PHP_EOL;

usleep(500000);

// Query
$getAllBy = $container->getByType(\Spameri\Elastic\Model\GetAllBy::class);
$elasticQuery = new \Spameri\ElasticQuery\ElasticQuery();
$elasticQuery->addMustQuery(new \Spameri\ElasticQuery\Query\MatchAll());
$result = $getAllBy->execute($elasticQuery, 'test_pagination');
echo 'Total found: ' . $result->stats()->total() . PHP_EOL;
