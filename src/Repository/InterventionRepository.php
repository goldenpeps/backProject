<?php

namespace App\Repository;

use App\Entity\Intervention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Intervention>
 */
class InterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervention::class);
    }

    public function existsConflictForEquipeOnSlot(
        int $equipeId,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        ?int $excludeInterventionId = null
    ): bool {
        $qb = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->innerJoin('i.equipeIntevention', 'ei')
            ->andWhere('ei.id = :equipeId')
            ->andWhere('i.datePrevue <= :end')
            ->andWhere('i.dateRealisation >= :start')
            ->setParameter('equipeId', $equipeId)
            ->setParameter('start', $start, 'datetime_immutable')
            ->setParameter('end', $end, 'datetime_immutable');

        if ($excludeInterventionId !== null) {
            $qb->andWhere('i.id != :excludeInterventionId')
                ->setParameter('excludeInterventionId', $excludeInterventionId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @param int[] $utilisateurIds
     */
    public function existsConflictForUtilisateursOnSlot(
        array $utilisateurIds,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        ?int $excludeInterventionId = null
    ): bool {
        if ($utilisateurIds === []) {
            return false;
        }

        $qb = $this->createQueryBuilder('i')
            ->select('COUNT(DISTINCT i.id)')
            ->innerJoin('i.equipeIntevention', 'ei')
            ->innerJoin('ei.utilisateurs', 'u')
            ->andWhere('u.id IN (:utilisateurIds)')
            ->andWhere('i.datePrevue <= :end')
            ->andWhere('i.dateRealisation >= :start')
            ->setParameter('utilisateurIds', $utilisateurIds)
            ->setParameter('start', $start, 'datetime_immutable')
            ->setParameter('end', $end, 'datetime_immutable');

        if ($excludeInterventionId !== null) {
            $qb->andWhere('i.id != :excludeInterventionId')
                ->setParameter('excludeInterventionId', $excludeInterventionId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function existsConflictForMaterielOnSlot(
        int $materielUtiliseId,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        ?int $excludeInterventionId = null
    ): bool {
        $qb = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->innerJoin('i.materielUtilise', 'mu')
            ->andWhere('mu.id = :materielUtiliseId')
            ->andWhere('i.datePrevue <= :end')
            ->andWhere('i.dateRealisation >= :start')
            ->setParameter('materielUtiliseId', $materielUtiliseId)
            ->setParameter('start', $start, 'datetime_immutable')
            ->setParameter('end', $end, 'datetime_immutable');

        if ($excludeInterventionId !== null) {
            $qb->andWhere('i.id != :excludeInterventionId')
                ->setParameter('excludeInterventionId', $excludeInterventionId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function findPlanningForUtilisateur(
        int $utilisateurId,
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $to = null
    ): array {
        $qb = $this->createQueryBuilder('i')
            ->innerJoin('i.equipeIntevention', 'ei')
            ->innerJoin('ei.utilisateurs', 'u')
            ->andWhere('u.id = :utilisateurId')
            ->setParameter('utilisateurId', $utilisateurId)
            ->orderBy('i.datePrevue', 'ASC');

        if ($from !== null) {
            $qb->andWhere('i.dateRealisation >= :from')
                ->setParameter('from', $from, 'datetime_immutable');
        }

        if ($to !== null) {
            $qb->andWhere('i.datePrevue <= :to')
                ->setParameter('to', $to, 'datetime_immutable');
        }

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Intervention[] Returns an array of Intervention objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Intervention
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
