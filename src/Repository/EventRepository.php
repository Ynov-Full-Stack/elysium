<?php

namespace App\Repository;

use App\Entity\Event;
use App\Enum\EventType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function findFilteredPaginated(Request $request, int $page = 1, int $limit = 12): array
    {
        $qb = $this->createQueryBuilder('e');

        // Filtres dynamiques
        if ($search = $request->query->get('search')) {
            $qb->andWhere('e.name LIKE :search')->setParameter('search', "%$search%");
        }
        if ($types = $request->query->all('types')) {
            $qb->andWhere('e.type IN (:types)')->setParameter('types', $types);
        }
        $qb->andWhere('e.price <= :maxPrice')->setParameter('maxPrice', $request->query->get('max_price', 200));
        if ($dateFrom = $request->query->get('date_from')) {
            $qb->andWhere('e.eventDate >= :dateFrom')->setParameter('dateFrom', new \DateTime($dateFrom));
        }
        if ($city = $request->query->get('city')) {
            $qb->andWhere('e.city LIKE :city')->setParameter('city', "%$city%");
        }

        $qb->orderBy('e.eventDate', 'ASC');

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($qb);
        $paginator->getQuery()->setFirstResult(($page-1)*$limit)->setMaxResults($limit);

        return [
            'events' => iterator_to_array($paginator),
            'totalItems' => count($paginator),
            'currentPage' => $page,
            'totalPages' => ceil(count($paginator) / $limit)
        ];
    }

    public function findDistinctLieux(): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('DISTINCT e.city')
            ->where('e.city IS NOT NULL')
            ->orderBy('e.city', 'ASC');

        return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);
    }

    public function findDistinctTypes(): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('DISTINCT e.type')
            ->where('e.type IS NOT NULL')
            ->orderBy('e.type', 'ASC');

        $intValues = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);

        $cases = EventType::cases();
        $names = [];
        foreach ($intValues as $intVal) {
            $names[] = $cases[$intVal]->name;
        }
        return array_unique($names);
    }



    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
