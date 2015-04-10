<?php

namespace Dwf\PronosticsBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Dwf;

/**
 * StandingRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StandingRepository extends EntityRepository
{
    public function findByUserAndEventAndGame(Dwf\PronosticsBundle\Entity\User $user, Dwf\PronosticsBundle\Entity\Event $event, Dwf\PronosticsBundle\Entity\Game $game)
    {
        $qb = $this->createQueryBuilder('s')
        ->where('s.user = :user')
        ->setParameter('user', $user)
        ->andWhere('s.event = :event')
        ->setParameter('event', $event)
        ->andWhere('s.game = :game')
        ->setParameter('game', $game)
        ;
        $query = $qb->getQuery();
        return $query->getResult();
    }
    
    public function getMaxPointsByUserAndEventBeforeGame(Dwf\PronosticsBundle\Entity\User $user, Dwf\PronosticsBundle\Entity\Event $event, Dwf\PronosticsBundle\Entity\Game $game)
    {
        $qb = $this->createQueryBuilder('s')
        ->where('s.user = :user')
        ->leftJoin('s.game','g')
        ->setParameter('user', $user)
        ->andWhere('s.event = :event')
        ->setParameter('event', $event)
        ->andWhere('g.id < :game')
        ->setParameter('game', $game->getId())
        ->orderBy('s.points', 'DESC')
        ->setMaxResults(1)
        ;
        $query = $qb->getQuery();
        return $query->getResult();
    }
    
    public function getByEventAndGroup(Dwf\PronosticsBundle\Entity\Event $event, $group)
    {
        $qb = $this->createQueryBuilder('s')
        ->leftJoin('s.user','u')
        ->leftJoin('u.groups', 'g')
        ->select('s','MAX(s.points) AS total', 'MAX(s.pronostics) AS nb_pronostics')
        ->where('g.id IN (:group)')
        ->setParameter('group', $group)
        ->andWhere('s.event = :event')
        ->setParameter('event', $event)
        ->groupBy('s.user')
        ->orderBy('total', 'DESC')
        
//         ->where('s.user = :user')
//         ->setParameter('user', $user)
//         ->andWhere('s.event = :event')
//         ->setParameter('event', $event)
//         ->andWhere('s.game = :game')
//         ->setParameter('game', $game)
        ;
        $query = $qb->getQuery();
        return $query->getResult();
    }
}
