<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function getAllProductsInStorageBetweenPrice(string $minPrice, string $maxPrice): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.unitPrice BETWEEN :minPrice AND :maxPrice')
            ->andWhere('p.storage > 0')
            ->setParameter('minPrice', $minPrice)
            ->setParameter('maxPrice', $maxPrice)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function getAllProductsByCategory(int $categoryId) : array
    {
        return $this->createQueryBuilder('p')
            ->select(['p.id', 'p.name'])
            ->innerJoin('p.category', 'c')
            ->where('c.id = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function getProductsWithConditions(\DateTime $createdAt, float $price, int $categoryId) : array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.category', 'c')
            ->where('c.id = :categoryId')
            ->andWhere('p.unitPrice > :price')
            ->andWhere('p.createdAt > :createdAt')
            ->setParameter('categoryId', $categoryId)
            ->setParameter('price', $price)
            ->setParameter('createdAt', $createdAt)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getArrayResult();
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
}
