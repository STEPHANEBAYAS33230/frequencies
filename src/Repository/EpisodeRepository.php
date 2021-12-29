<?php

namespace App\Repository;

use App\Entity\Episode;
use App\Entity\Serie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Episode|null find($id, $lockMode = null, $lockVersion = null)
 * @method Episode|null findOneBy(array $criteria, array $orderBy = null)
 * @method Episode[]    findAll()
 * @method Episode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EpisodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Episode::class);
    }

    public function trouverEpiOrdre()
    {    //trier les episode par serie et par n° ordre

        return $this->createQueryBuilder('e')
            ->orderBy('e.serie', 'ASC')
            ->orderBy('e.numero', 'ASC')
            ->getQuery()
            ->getResult();

    }
    public function findSinumberUtiliser(Serie $laSerieChoisie,$numerochoisi)
    {    //recherche numero ordre deja utilisé ds la serie
        return $this->createQueryBuilder('e')
            ->Where('e.serie = :laSerieChoisie')
            ->andWhere('e.numero = :numerochoisi')
            ->setParameter('laSerieChoisie', $laSerieChoisie)
            ->setParameter('numerochoisi', $numerochoisi)
            ->getQuery()
            ->getResult();

    }

    // /**
    //  * @return Episode[] Returns an array of Episode objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Episode
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
