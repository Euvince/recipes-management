<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Recipe>
 *
 * @method Recipe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Recipe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Recipe[]    findAll()
 * @method Recipe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PaginatorInterface $paginator
    )
    {
        parent::__construct($registry, Recipe::class);
    }

    function findTotalDuration () : int
    {
        return (int)
            $this->createQueryBuilder('r')
            ->select('SUM(r.duration) AS total)')
            ->getQuery()
            ->getScalarResult()
        ;
    }

    function findWithDurationLowerThan (int $duration) : array
    {
        return 
            $this->createQueryBuilder('r')
            ->where('r.duration < :duration')
            ->setParameter('duration', $duration)
            ->getQuery()
            ->getResult()
        ;
    }

    function paginateRecipes (int $page, int $limit, ?int $userId) : PaginationInterface
    {
        $builder = $this->createQueryBuilder('r');

        if ($userId) {
            $builder = $builder->andWhere('r.user = :user')->setParameter('user', $userId);
        }

        return
            $this->paginator->paginate(
                $builder,
                $page, $limit, [
                    'distinct' => true,
                    'sortAllowFieldList' => ['r.id', 'r.title']
                ]
            )
        ;
    }

//    /**
//     * @return Recipe[] Returns an array of Recipe objects
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

//    public function findOneBySomeField($value): ?Recipe
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
