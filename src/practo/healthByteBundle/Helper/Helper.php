<?php

/**
 * Created by PhpStorm.
 * User: RDVs
 * Date: 27/07/15
 * Time: 4:52 PM.
 */
namespace practo\healthByteBundle\Helper;

use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use FOS\RestBundle\Util\Codes;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class Helper.
 */
class Helper
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var
     */
    protected $cacheUtils;


    /**
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine
     * @param \Symfony\Bridge\Monolog\Logger           $logger
     */
    public function __construct(Doctrine $doctrine, Logger $logger)
    {
        $this->entityManager = $doctrine->getManager();

        $dLogger = new \Doctrine\DBAL\Logging\DebugStack();

        $doctrine->getConnection()
            ->getConfiguration()
            ->setSQLLogger($dLogger);

        $logger->info(json_encode($dLogger->queries));
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|\Doctrine\ORM\EntityManager|object
     */
    public function getEntitiesManager()
    {
        return $this->entityManager;
    }

    /**
     * @param String $entityName
     *
     * @return array|null
     */
    public function loadAll($entityName)
    {
        $entity = $this->entityManager->getRepository($entityName)->findAll();

        if (empty($entity)) {
            return;
        }

        return $entity;
    }

    /**
     * @param int    $id
     * @param string $entityName
     *
     * @return mixed
     */
    public function loadById($id, $entityName)
    {
        $entity = $this->entityManager->getRepository($entityName)->find($id);

        if (empty($entity)) {
            return;
        }

        return $entity;
    }

    /**
     * @param string $entityName
     *
     * @return EntityRepository|null
     */
    public function getRepository($entityName)
    {
        $entityRepository = $this->entityManager->getRepository($entityName);

        if (is_null($entityRepository)) {
            return;
        }

        return $entityRepository;
    }

    /**
     * @param BaseEntity $entity
     */
    public function remove($entity)
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    /**
     * @param mixed $entity
     * @param bool  $flush
     */
    public function persist($entity, $flush = null)
    {
        if ($entity !== null) {
            $this->entityManager->persist($entity);
        }

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * @param mixed $entity
     * @param bool  $flush
     */
    public function merge($entity, $flush = null)
    {
        if ($entity !== null) {
            $this->entityManager->merge($entity);
        }
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * flush
     */
    public function flush()
    {
        $this->entityManager->flush();
    }

    /**
     * @param string $date
     *
     * @return bool
     */
    public function verifyDate($date)
    {
        return (\DateTime::createFromFormat('Y-m-d', $date) !== false);
    }

}
