<?php

namespace App\Repository;

use App\Entity\Log;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Log>
 *
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Log::class);
    }

    public function add(Log $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Log $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array<mixed>|null
     */
    public function getLogsByUsername(string $username, ?int $limit = null): ?array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.user', 'u')
            ->andWhere('u.username = :username')
            ->setParameter('username', $username)
            ->addOrderBy('l.createdAt', 'DESC');
        if (null !== $limit) {
            $qb->setFirstResult(0)
                ->setMaxResults($limit);
        }
        return $qb->getQuery()
            ->getResult();
    }

    /**
     * @return Log[]
     */
    public function getLogs(?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('l')
            ->leftJoin('l.user', 'u')
            ->addOrderBy('l.createdAt', 'DESC');
        if (null !== $limit) {
            $qb->setFirstResult(0)
                ->setMaxResults($limit);
        }
        return $qb->getQuery()
            ->getResult();
    }

    public function getLogsPage(int $start = 0, int $limit = 20): int
    {
        $qb = $this->createQueryBuilder("l")
            ->leftJoin('l.user', 'u')
            ->addOrderBy('l.createdAt', 'DESC');

        $qb->setFirstResult($start)
            ->setMaxResults($limit);

        return count(new Paginator($qb));
    }

    /**
     * @return Log[]
     */
    public function getLogsWithPosition(int $start = 0, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder("l")
            ->leftJoin('l.user', 'u')
            ->addOrderBy('l.createdAt', 'DESC');

        return $qb->setFirstResult($start)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
