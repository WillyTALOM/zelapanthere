<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Product[] Returns an array of Product objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

        public function findBestSellers(): array
        {
            return $this->createQueryBuilder('p')
                ->innerJoin('App\Entity\OrderDetails', 'od', 'WITH', 'p.id = od.product_id')
                ->groupBy('od.product_id')
                ->orderBy('SUM(od.quantity)', 'DESC')
                ->setMaxResults(8)
                ->getQuery()
                ->getResult()
            ;
        }

    //     public function findLastEight(): array
    // {
    //     return $this->createQueryBuilder('p')
    //         ->orderBy('p.id ', 'DESC')
    //         ->setMaxResults(8)
    //         ->getQuery()
    //         ->getResult();
    // }

    public function findLastEight(): array
    {
        // $db = $this->getEntityManager()->getConnection();
        // $req = $db->prepare('SELECT * FROM product ORDER BY created_at DESC, id DESC LIMIT 8');
        // $results = $req->executeQuery();
        // return $results->fetchAllAssociative();

        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT p
            FROM App\Entity\Product p
            ORDER BY p.created_at DESC, p.id DESC'
        )->setMaxResults(8);
        return $query->getResult();
    }

    public function findRandom(int $value)
    {
        $db = $this->getEntityManager()->getConnection();
        $req = $db->prepare('SELECT id FROM product ORDER BY rand() LIMIT ' . $value);
        $results = $req->executeQuery();
        return $results->fetchAllAssociative();
    }

    
}
