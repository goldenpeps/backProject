<?php

namespace App\Repository;

use App\Entity\EquipeIntervention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EquipeIntervention>
 */
class EquipeInterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipeIntervention::class);
    }

    public function searchByDateTerrainUtilisateur($date = null, $terrainId = null, $utilisateurId = null): array
    {
        $qb = $this->createQueryBuilder('ei')
            ->leftJoin('ei.interventions', 'i')
            ->leftJoin('ei.utilisateurs', 'u');
        
        if ($date !== null) {
            if (is_string($date)) {
                $date = new \DateTimeImmutable($date);
            }
            $qb->andWhere('i.datePrevue = :date')
                ->setParameter('date', $date->format('Y-m-d'));
        }
        
        if ($terrainId !== null) {
            $qb->innerJoin('i.terrain', 't')
                ->andWhere('t.id = :terrainId')
                ->setParameter('terrainId', $terrainId);
        }
        if ($utilisateurId !== null) {
            $qb->andWhere('u.id = :utilisateurId')
                ->setParameter('utilisateurId', $utilisateurId);
        }
        

        return $qb->getQuery()->getResult();
    }

    public function existsByInterventionAndUtilisateur(int $interventionId, int $utilisateurId): bool
    {
        $result = $this->createQueryBuilder('ei')
            ->innerJoin('ei.interventions', 'i')
            ->innerJoin('ei.utilisateurs', 'u')
            ->andWhere('i.id = :interventionId')
            ->andWhere('u.id = :utilisateurId')
            ->setParameter('interventionId', $interventionId)
            ->setParameter('utilisateurId', $utilisateurId)
            ->getQuery()
            ->getResult();
        
        return count($result) > 0;
    }
    //    /**
    //     * @return EquipeIntervention[] Returns an array of EquipeIntervention objects
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

    //    public function findOneBySomeField($value): ?EquipeIntervention
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
