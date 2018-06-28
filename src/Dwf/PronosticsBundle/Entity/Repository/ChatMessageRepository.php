<?php

namespace Dwf\PronosticsBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Dwf\PronosticsBundle\Entity\Contest;

class ChatMessageRepository extends EntityRepository
{

    /**
     * @param Contest $contest
     * @param int $limit
     * @return array
     */
    public function getLastMessagesByContest(Contest $contest, $limit = 50)
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.contest = :contest')
            ->setParameter('contest', $contest)
            ->orderBy('c.createdAt', 'ASC')
            ->setMaxResults($limit)
        ;
        $query = $qb->getQuery();

        return $query->getResult();
    }
}
