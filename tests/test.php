<?php
require_once __DIR__.'/../vendor/autoload.php'; // Autoload files using Composer autoload

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use WSApigilityDoctrine\DoctrineAdapter;

$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__.'/../src'), TRUE);

$conn = array(
    'driver' => 'pdo_sqlite',
    'path' => __DIR__.'/db.sqlite',
);

$em = EntityManager::create($conn, $config);

$qb = $em->createQueryBuilder();

$qb->select('e')
    ->from('Row', 'e')
    ->orderBy('e.id', 'ASC');
    
$params = ['query' => 'like(name,row2*)'];

$results = 3;

$adapter = new DoctrineAdapter($qb, $params, $results);