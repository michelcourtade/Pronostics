<?php

namespace Dwf\PronosticsBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Dwf;

/**
 * InvitationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class InvitationRepository extends EntityRepository
{
    public function findAllByUserAndContestAndEmail(Dwf\PronosticsBundle\Entity\User $user, Dwf\PronosticsBundle\Entity\Contest $contest, $email)
    {
        $qb = $this->createQueryBuilder('i')
        ->where('i.user = :user')
        ->setParameter('user', $user)
        ->andWhere('i.contest = :contest')
        ->setParameter('contest', $contest);
        if($email === null)
            $qb = $qb->andWhere('i.email IS NULL');
        else {
            $qb = $qb->andWhere('i.email = :email')
                ->setParameter('email', $email)
            ;
        }
    
        $query = $qb->getQuery();
        return $query->getOneOrNullResult();
    }
}