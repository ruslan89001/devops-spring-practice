<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findBySpace(Space $space): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.user', 'u')
            ->where('r.space = :space')
            ->setParameter('space', $space)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllWithDetails(): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.user', 'u')
            ->join('r.space', 's')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getAverageRatingForSpace(Space $space): ?float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avgRating')
            ->where('r.space = :space')
            ->setParameter('space', $space)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? round((float)$result, 1) : null;
    }

    public function findByUserAndSpace(User $user, Space $space): ?Review
    {
        return $this->findOneBy([
            'user' => $user,
            'space' => $space
        ]);
    }

    public function countReviews(): int
    {
        return $this->count([]);
    }

    public function save(Review $review, bool $flush = true): void
    {
        $this->getEntityManager()->persist($review);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Review $review, bool $flush = true): void
    {
        $this->getEntityManager()->remove($review);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
