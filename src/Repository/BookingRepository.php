<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.space', 's')
            ->where('b.user = :user')
            ->setParameter('user', $user)
            ->orderBy('b.bookingDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function createBookingTransactional(User $user, Space $space, \DateTimeInterface $date): Booking
    {
        $booking = new Booking();
        $booking->setUser($user);
        $booking->setSpace($space);
        $booking->setBookingDate($date);
        $booking->setStatus('active');

        $em = $this->getEntityManager();

        try {
            $em->persist($booking);
            $em->flush();
            return $booking;
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            throw new \RuntimeException('Space is already booked for this date');
        }
    }

    public function cancelBooking(Booking $booking): void
    {
        $booking->setStatus('cancelled');
        $this->getEntityManager()->flush();
    }

    public function save(Booking $booking, bool $flush = true): void
    {
        $this->getEntityManager()->persist($booking);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Booking $booking, bool $flush = true): void
    {
        $this->getEntityManager()->remove($booking);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
