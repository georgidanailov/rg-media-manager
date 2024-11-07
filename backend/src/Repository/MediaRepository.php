<?php

namespace App\Repository;

use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Media>
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    //    /**
    //     * @return Media[] Returns an array of Media objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Media
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findFilesOlderThan (\DateTime $date){
        return $this->createQueryBuilder('m')
            ->where('m.deleted_at IS NOT NULL')
            ->andWhere('m.deleted_at <= :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    public function getTotalStoragePerUser()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('u.id AS user_id, u.name, SUM(m.file_size) AS totalStorage')
            ->from('App\Entity\Media', 'm')
            ->leftJoin('m.user', 'u')
            ->groupBy('u.id')
            ->orderBy('totalStorage', 'DESC');

        return $qb->getQuery()->getArrayResult();
    }

    public function getFileTypesPerUser(): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('u.id as user_id', 'u.name',
            "SUM(CASE WHEN m.file_type = 'image' THEN 1 ELSE 0 END) as images",
            "SUM(CASE WHEN m.file_type = 'video' THEN 1 ELSE 0 END) as videos",
            "SUM(CASE WHEN m.file_type = 'document' THEN 1 ELSE 0 END) as documents",
            "SUM(CASE WHEN m.file_type = 'archive' THEN 1 ELSE 0 END) as archives")
            ->from('App\Entity\User', 'u')
            ->leftJoin('u.media', 'm')
            ->groupBy('u.id');

        return $qb->getQuery()->getArrayResult();

    }

}
