<?php

namespace App\Repository;

use App\Entity\GameServer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GameServer>
 *
 * @method GameServer|null find($id, $lockMode = null, $lockVersion = null)
 * @method GameServer|null findOneBy(array $criteria, array $orderBy = null)
 * @method GameServer[]    findAll()
 * @method GameServer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameServerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GameServer::class);
    }

    public function findById(int $id): ?GameServer
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return GameServer[]
     */
    public function findByUsername(string $username): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.users', 'u')
            ->addSelect('u')
            ->andWhere('u.username = :username')
            ->setParameter('username', $username)
            ->addOrderBy('g.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return GameServer[]
     */
    public function findAllWithOrder(): array
    {
        return $this->createQueryBuilder('g')
            ->addOrderBy('g.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
