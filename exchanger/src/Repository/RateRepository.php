<?php

namespace App\Repository;

use App\Entity\Rate;
use App\Structures\PaginatedData;

/**
 * @method Rate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rate[]    findAll()
 * @method Rate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RateRepository extends BaseRepository
{
    /** @var string */
    protected $entityClass = Rate::class;

    /** @var array */
    protected $baseFilter = [
        'deleted' => [
            false
        ]
    ];

    /**
     * @param int $page
     * @return PaginatedData
     */
    public function getRates(int $page = 1): PaginatedData
    {
        $queryBuilder = $this->createQueryBuilder('qb')
            ->orderBy('qb.currency', 'ASC');

        return $this->paginate($page, $queryBuilder);
    }
}
