# warpsale/ws-apigility-doctrine

Warpsale Doctrine integration with Apigility

Installation (with composer):

 * Install Apigility;
 * Install Doctrine;
 * Install doctrine/doctrine-orm-module;
 * Install warpsale/ws-apigility-doctrine.

Setup:

 * Copy the "_utils/module/Base" folder to the "/module" folder of Apigility;
 * Add the "Base" module to the "/config/modules.config.php" Apigility file;
 * Append to the "/config/autoload/local.php" Apigility file:
 ```php
 'doctrine' => array(
 'connection' => array(
    'orm_default' => array(
        'driverClass' => 'Doctrine\\DBAL\\Driver\\PDOPgSql\\Driver',
        'params' => array(
            'host' => 'hostname',
            'port' => '5432',
            'user' => 'username',
            'password' => 'password',
            'dbname' => 'database',
        ),
    ),
),
'hydrators' => [
'initializers' => [
    'Base\V1\Model\BaseHydratorInitializer',
],
],
 ```
 * Create a new API (Geo, for example).
 * Create a new REST Service (Region, for example)
 * Create the folder "Model" inside the "/module/Geo/src/Geo/V1" Apigility folder and Copy your Doctrine Entity Files. Region.php example:
 ```php
 <?php
namespace Geo\V1\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Base\V1\Model\BaseReadEntity;


/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="region", 
 *      uniqueConstraints={@ORM\UniqueConstraint(name="geo__region__unm49__key", columns={"unm49"})}, 
 *      indexes={@ORM\Index(name="geo__region__level__idx", columns={"level"})}, 
 *      schema="geo"
 * )
 */
class Region extends BaseReadEntity
{
    /**
     * @ORM\Id @ORM\Column(type="smallint", options={"comment":"custom shorted hierarchical code"})
     * @var int
     */
    private $id;
    
    /** 
     * @ORM\Column(type="string", length=3, options={"comment":"united nations UN M.49 code"}) 
     * @var string
     */
    private $unm49;
    
    /** 
     * @ORM\ManyToOne(targetEntity="Region", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="RESTRICT") 
     * @var int
     */
    private $parent;
    
    /**
     * @ORM\OneToMany(targetEntity="Region", mappedBy="parent")
     * @ORM\OrderBy({"id"="ASC"})
     * @var array
     */
    private $children;
    
    /**
     * @ORM\OneToMany(targetEntity="Country", mappedBy="region")
     * @ORM\OrderBy({"id"="ASC"})
     * @var array
     */
    private $countries;
    
    /** 
     * @ORM\Column(type="smallint")
     * @var int
     */
    private $level;
    
    /** 
     * @ORM\Column(type="string", length=32) 
     * @var string
     */
    private $name;
    
    /**
     * @return the $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return the $unm49
     */
    public function getUnm49()
    {
        return $this->unm49;
    }

    /**
     * @return the $parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return the $children
     */
    public function getChildren()
    {
        return $this->children;
    }
    
    /**
     * @return the $countries
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * @return the $level
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return the $name
     */
    public function getName()
    {
        return $this->name;
    }

    public function __construct() 
    {
        $this->children = new ArrayCollection();
        $this->countries = new ArrayCollection();
    }
}
 ```
 * append to the "/module/Geo/config/module.config.php" file:
 ```php
 'doctrine' => array(
    'driver' => array(
        'geo_model' => array(
            'class' => 'Doctrine\\ORM\\Mapping\\Driver\\AnnotationDriver',
            'cache' => 'array',
            'paths' => array(
                0 => __DIR__ . '/../src/Geo/V1/Model',
            ),
        ),
        'orm_default' => array(
            'drivers' => array(
                'Geo\\V1\\Model' => 'geo_model',
            ),
        ),
    ),
), 
 ```
 * Apply the "DoctrineModule\Stdlib\Hydrator\DoctrineObject" to the "Hydrator Service Name"
 * Add "page", "query" and "sort" words to the "Collection Query String Whitelist";
* Change the "Entity Class" to "Geo\V1\Model\Region" and, after the service created, delete the file "/module/Geo/src/Geo/V1/Rest/Region/RegionEntity.php" of Apigility;
* This is a readonly example, so in sections "HTTP Entity Methods" and "HTTP Collection Methods", select the "GET" options;
* In "Page Size Parameter", write "results";
* Save;
* Create the file "RegionMapper.php" inside the "/module/Geo/src/Geo/V1/Rest/Region" Apigility folder;
* Write the following code:
 ```php
<?php
namespace Geo\V1\Rest\Region;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use WSApigilityDoctrine\DoctrineFilter;
use WSApigilityDoctrine\DoctrineSort;
use WSApigilityDoctrine\ReadMapperInterface;
use Geo\V1\Model\Region as RegionEntity;

class RegionMapper implements ReadMapperInterface
{
    protected $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    public function fetch($id)
    {
        $qb = $this->em->createQueryBuilder();
        
        $qb->select('e')
            ->from(RegionEntity::class, 'e')
            ->where('e.id = :id');
        
        $qb->setParameters(array(
            'id' => $id
        ));
        
        return $qb->getQuery()->getOneOrNullResult(); 
    }

    public function fetchAll($params = array())
    {
        $qb = $this->em->createQueryBuilder();
        
        $qb->select('e')
            ->from(RegionEntity::class, 'e')
            ->leftJoin('e.parent', 'p')
            ->add('orderBy', 'e.level ASC, e.id ASC');
        
        // QUERY (RQL) AND SORT
        DoctrineFilter::filter($qb, $params);
        DoctrineSort::sort($qb, $params);

        // PAGINATE RESULTS
        $paginator = new Paginator($qb, $fetchJoinCollection = true);
        $adapter = new DoctrinePaginator($paginator);
        
        return new RegionCollection($adapter);
    }
}
 ```
* edit the "/module/Geo/src/Geo/V1/Rest/Region/RegionResource.php" Apigility file and:
	* create the "mapper" protected propety:
 ```php
    protected $mapper;
 ```
	* create a constructor:
 ```php
    public function __construct(RegionMapper $mapper)
    {
        $this->mapper = $mapper;
    }
 ```
	* change the "GET" methods:
 ```php
    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        return $this->mapper->fetch($id);
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = array())
    {
        return $this->mapper->fetchAll($params);
    }
 ```
* edit the "/module/Geo/src/Geo/V1/Rest/Region/RegionResourceFactory.php" Apigility file:
 ```php
<?php
namespace Geo\V1\Rest\Region;

use Doctrine\ORM\EntityManager;
use Geo\V1\Rest\Region\RegionMapper;
use Geo\V1\Rest\Region\RegionResource;

class RegionResourceFactory
{
    public function __invoke($services)
    {
        $em = $services->get(EntityManager::class);
        $mapper = new RegionMapper($em);
        return new RegionResource($mapper);
    }
}
 ```
* Test results (example):
	* http://api.localhost/region/200
	* http://api.localhost/region?query=like(name,Europe)&sort=-id&results=5

