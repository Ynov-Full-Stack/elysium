<?php

namespace App\Repository;

use App\Entity\Event;
use App\Enum\EventType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @throws \Exception
     */
    public function findFilteredPaginated(Request $request, int $page = 1, int $limit = 12): array
    {
        $qb = $this->createQueryBuilder('e');

        // just futur event and not past
        $qb->andWhere('e.eventDate >= :now')
            ->setParameter('now', new \DateTime());

        if ($search = $request->query->get('search')) {
            $qb->andWhere('e.name LIKE :search')->setParameter('search', "%$search%");
        }
        if ($types = $request->query->all('types')) {
            $typeValues = [];
            $cases = EventType::cases();

            foreach ($types as $typeName) {
                foreach ($cases as $case) {
                    if ($case->name === $typeName) {
                        $typeValues[] = $case->value;
                        break;
                    }
                }
            }

            if (!empty($typeValues)) {
                $qb->andWhere('e.type IN (:types)')
                    ->setParameter('types', $typeValues);
            }
        }
        $maxPrice = $request->query->get('max_price');
        if ($maxPrice !== null) {
            $qb->andWhere('e.price <= :maxPrice')->setParameter('maxPrice', $maxPrice);
        }
        if ($dateFrom = $request->query->get('date_from')) {
            $qb->andWhere('e.eventDate >= :dateFrom')->setParameter('dateFrom', new \DateTime($dateFrom));
        }
        if ($city = $request->query->get('city')) {
            $qb->andWhere('LOWER(e.city) LIKE LOWER(:city)')
                ->setParameter('city', "%$city%");
        }

        $qb->orderBy('e.eventDate', 'ASC');

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($qb);
        $paginator->getQuery()->setFirstResult(($page-1)*$limit)->setMaxResults($limit);
        $totalItems = count($paginator);

        return [
            'events' => iterator_to_array($paginator),
            'totalItems' => $totalItems,
            'currentPage' => $page,
            'totalPages' => ceil($totalItems / $limit)
        ];
    }


    public function findDistinctCities(): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('DISTINCT e.city')
            ->where('e.city IS NOT NULL')
            ->orderBy('e.city', 'ASC');

        return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);
    }

    public function findDistinctTypes(TranslatorInterface $translator): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('DISTINCT e.type')
            ->where('e.type IS NOT NULL')
            ->orderBy('e.type', 'ASC');

        $intValues = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);

        $cases = EventType::cases();
        $translatedNames = [];

        foreach ($intValues as $intVal) {
            if (isset($cases[$intVal])) {
                $translatedNames[] = $cases[$intVal]->trans($translator);
            }
        }

        return array_unique($translatedNames);
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
