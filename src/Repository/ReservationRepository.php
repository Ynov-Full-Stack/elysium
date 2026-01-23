<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findForReminder(\DateInterval $interval, int $toleranceMinutes = 5): array {
        $now = new \DateTimeImmutable();
        
        $targetTime = $now->add($interval);

        $start = $targetTime->sub(new \DateInterval("PT{$toleranceMinutes}M"));
        $end   = $targetTime->add(new \DateInterval("PT{$toleranceMinutes}M"));
        
        return $this->createQueryBuilder('r')
            ->join('r.event', 'e')
            ->where('e.eventDate BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    public function getAccountStat(User $user):array
    {
        $now = new \DateTimeImmutable();

        $result = $this->createQueryBuilder('r')
            ->select('
            SUM(CASE WHEN e.eventDate >= :now THEN 1 ELSE 0 END) AS upcoming,
            SUM(CASE WHEN e.eventDate < :now THEN 1 ELSE 0 END) AS past,
            COALESCE(SUM(r.seatQuantity * e.price), 0) AS total_spent
        ')
            ->join('r.event', 'e')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleResult();

        return [
            'upcoming' => (int) $result['upcoming'],
            'past' => (int) $result['past'],
            'total_spent' => round((float) $result['total_spent'], 2),
        ];

    }

    public function getUpcomingReservations(User $user, int $limit = 3):array
    {
        $now = new \DateTimeImmutable();
        $qb = $this->createQueryBuilder('r')
            ->select('
    e.id, e.name, e.type, e.eventDate as event_date, e.price,
    r.seatQuantity as seat_quantity,
    CONCAT(
        COALESCE(e.streetNumber, \'\'),
        CASE
            WHEN COALESCE(e.streetNumber, \'\') != \'\' AND e.street IS NOT NULL
            THEN CONCAT(\', \', e.street)
            ELSE \'\'
        END,
        CASE
            WHEN e.postalCode IS NOT NULL AND e.city IS NOT NULL
            THEN CONCAT(\', \', e.postalCode, \' \', e.city)
            ELSE CASE
                WHEN e.city IS NOT NULL
                THEN CONCAT(\', \', e.city)
                ELSE \'\'
            END
        END
    ) as lieu
')
            ->join('r.event', 'e')
            ->where('r.user = :user')
            ->andWhere('e.eventDate >= :now')
            ->orderBy('e.eventDate', 'ASC')
            ->setParameter('user', $user)
            ->setParameter('now', $now)
            ->setMaxResults($limit);

        $results = $qb->getQuery()->getArrayResult();

        return array_map(function ($data) {
            return [
                'event' => [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'eventDate' => $data['event_date'],
                    'city' => $data['lieu'] ?: 'Lieu non précisé',
                    'price' => (float) $data['price'],
                ],
                'seatQuantity' => (int) $data['seat_quantity'],
            ];
        }, $results);
    }

    public function countReservationsByUserWithFilters(User $user, string $filter = 'all'): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->join('r.event', 'e')
            ->where('r.user = :user')
            ->setParameter('user', $user);

        $now = new \DateTimeImmutable('today');

        if ($filter === 'upcoming') {
            $qb->andWhere('e.eventDate >= :now')
                ->setParameter('now', $now);
        } elseif ($filter === 'past') {
            $qb->andWhere('e.eventDate < :now')
                ->setParameter('now', $now);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
    public function findByUserWithFilters(User $user, string $filter = 'all', int $page = 1, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('r')
            ->join('r.event', 'e')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.createdAt', 'DESC');

        $now = new \DateTimeImmutable('today');

        if ($filter === 'upcoming') {
            $qb->andWhere('e.eventDate >= :now')
                ->setParameter('now', $now);
        } elseif ($filter === 'past') {
            $qb->andWhere('e.eventDate < :now')
                ->setParameter('now', $now);
        }

        // Pagination manuelle
        $totalItems = $this->countReservationsByUserWithFilters($user, $filter);
        $totalPages = ceil($totalItems / $limit);

        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return [
            'items' => $qb->getQuery()->getResult(),
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => max(1, $totalPages),
                'totalItems' => $totalItems,
            ]
        ];
    }

    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
