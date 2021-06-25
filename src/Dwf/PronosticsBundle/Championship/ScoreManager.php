<?php

namespace Dwf\PronosticsBundle\Championship;

use Doctrine\ORM\EntityManagerInterface;
use Dwf\PronosticsBundle\Entity\Contest;
use Dwf\PronosticsBundle\Entity\Pronostic;
use Dwf\PronosticsBundle\Entity\User;
use Ob\HighchartsBundle\Highcharts\Highchart;

class ScoreManager
{
    protected $scores = array(
        'nbScores' => 0,
        'nbPerfectScores' => 0,
        'nbGoodScores' => 0,
        'nbBadScores' => 0,
        'totalScores' => 0,
    );

    /** @var EntityManagerInterface  */
    protected $entityManager;

    /**
     * * @param Highchart $highchart
     *
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Contest $contest
     * @param User    $user
     *
     * @return array
     */
    public function buildScoresForContestAndUser(Contest $contest, User $user)
    {
        $pronosticRepository = $this->entityManager->getRepository(Pronostic::class);
        $nb = $pronosticRepository->getNbByUserAndContest($user, $contest);
        if ($nb) {
            $this->scores['nbScores'] = $nb;
            $this->scores['nbPerfectScores'] = $pronosticRepository->getNbScoreByUserAndContestAndResult($user, $contest, Pronostic::NB_POINTS_EXACT_SCORE);
            $this->scores['nbGoodScores'] = $pronosticRepository->getNbScoreByUserAndContestAndResult($user, $contest, Pronostic::NB_POINTS_GOOD_SCORE);
            $this->scores['nbBadScores'] = $pronosticRepository->getNbScoreByUserAndContestAndResult($user, $contest, Pronostic::NB_POINTS_BAD_SCORE);
            $results = $pronosticRepository->getResultsByContestAndUser($contest, $user);
            if ($results) {
                $this->scores['totalScores'] = $results['total'];
            }
        }

        return $this->scores;
    }
}