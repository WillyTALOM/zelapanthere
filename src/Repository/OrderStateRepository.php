<?php

namespace App\Repository;

use App\Entity\OrderState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderState>
 *
 * @method OrderState|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderState|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderState[]    findAll()
 * @method OrderState[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderState::class);
    }

    public function save(OrderState $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OrderState $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return OrderState[] Returns an array of OrderState objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?OrderState
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


        // public function(EntityRepository $entityRepository)
        //  {
        //     return $entityRepository->createQueryBuilder('a')
        //     ->where('a.user = :val')
        //      ->setParameter(':val', $this->security->getUser());
        // }
}
