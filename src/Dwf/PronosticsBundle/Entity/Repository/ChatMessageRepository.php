<?php

namespace Dwf\PronosticsBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Dwf\PronosticsBundle\Entity\Contest;

class ChatMessageRepository extends EntityRepository
{

    /**
     * @param Contest $contest
     * @param int $first
     * @param int $limit
     * @return array
     */
    public function getLastMessagesByContest(Contest $contest, $first = 0, $limit = 20)
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.contest = :contest')
            ->setParameter('contest', $contest)
            ->orderBy('c.createdAt', 'ASC')
            ->setFirstResult($first)
            ->setMaxResults($limit)
        ;
        $query = $qb->getQuery();

        return $query->getResult();
    }
}
