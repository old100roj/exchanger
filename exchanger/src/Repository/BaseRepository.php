<?php

namespace App\Repository;

use App\Services\PaginationHelper;
use App\Structures\PaginatedData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BaseRepository extends ServiceEntityRepository
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var string */
    protected $entityClass;

    /** @var array */
    protected $baseFilter;

    public function __construct(RegistryInterface $registry, ObjectManager $objectManager)
    {
        parent::__construct($registry, $this->entityClass);

        $this->objectManager = $objectManager;
    }

    /**
     * @param object $entity
     */
    public function plush($entity): void
    {
        if (!$entity instanceof $this->entityClass) {
            return;
        }

        $this->objectManager->persist($entity);
        $this->objectManager->flush();
    }

    /**
     * @return ObjectManager
     */
    public function getObjectManager(): ObjectManager
    {
        return $this->objectManager;
    }

    /**
     * @param array $filter
     * @return int
     */
    public function getRecordsNumber(array $filter = []): int
    {
        try {
            $queryBuilder = $this->createQueryBuilder('qb')->select('count(1)');
            $this->applyFilter($queryBuilder, $filter);
            $result = $queryBuilder
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }

        return (int)array_shift($result);
    }

    /**
     * @param int $page
     * @param QueryBuilder|null $queryBuilder
     * @param array $filter
     * @return PaginatedData
     */
    public function paginate(int $page, ?QueryBuilder $queryBuilder = null, $filter = []): PaginatedData
    {
        if (is_null($queryBuilder)) {
            $queryBuilder = $this->createQueryBuilder('qb');
        }
        $this->applyFilter($queryBuilder, $filter);
        $paginatedData = new PaginatedData();
        $paginatedData->currentPage = $page;
        $paginatedData->lastPage = PaginationHelper::getLastPage($this->getRecordsNumber($filter));
        $paginatedData->records = $queryBuilder
            ->setMaxResults(PaginationHelper::getLimit())
            ->setFirstResult(PaginationHelper::getOffset($page))
            ->getQuery()
            ->getResult()
        ;

        return $paginatedData;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $filter
     */
    private function applyFilter(QueryBuilder $queryBuilder, array $filter = []): void
    {
        $filter = array_merge($this->baseFilter, $filter);

        $alias = $queryBuilder->getRootAliases()[0];

        foreach ($filter as $key => $valueAndCondition) {
            if (!array_key_exists(0, $valueAndCondition)) {
                continue;
            }

            $value = $valueAndCondition[0];
            $condition = '=';

            if (array_key_exists(1, $valueAndCondition)) {
                $condition = $valueAndCondition[1];
            }

            $queryBuilder
                ->andWhere($alias . '.' . $key . ' ' . $condition .  ' :val')
                ->setParameter('val', $value)
            ;
        }
    }
}
