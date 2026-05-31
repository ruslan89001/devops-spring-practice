<?php

namespace App\Repository;

use App\Entity\Space;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SpaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Space::class);
    }

    public function findActiveSpaces(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.status = :status')
            ->setParameter('status', 'active')
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveSpacesLimit(int $limit): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.status = :status')
            ->setParameter('status', 'active')
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getAllSpaces(): array
    {
        return $this->findAll();
    }

    public function countSpaces(): int
    {
        return $this->count([]);
    }

    public function countActiveSpaces(): int
    {
        return $this->count(['status' => 'active']);
    }

    public function save(Space $space, bool $flush = true): void
    {
        $this->getEntityManager()->persist($space);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Space $space, bool $flush = true): void
    {
        $this->getEntityManager()->remove($space);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
