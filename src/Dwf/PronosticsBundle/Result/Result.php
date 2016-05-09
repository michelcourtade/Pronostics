<?php

namespace Dwf\PronosticsBundle\Result;

use Dwf\PronosticsBundle\Entity\GameTypeResult;
use Dwf\PronosticsBundle\Entity\Standing;
class Result
{
    protected $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    public function setResultsForGame(\Dwf\PronosticsBundle\Entity\Game $game)
    {

        $pronostics = $this->em->getRepository('DwfPronosticsBundle:Pronostic')->findAllByGameAndResult($game, null);
        foreach ($pronostics as $pronostic)
        {
            $result = 0;
            //pronostic simple (1 N 2)
            if($game->getEvent()->getSimpleBet()) {
                if((($game->getWhoWin() == 1) && ($pronostic->getSimpleBet() == 1)) 
                    || (($game->getWhoWin() == 2) && ($pronostic->getSimpleBet() == 2)) 
                    || (($game->getWhoWin() == 0) && ($pronostic->getSimpleBet() == 'N'))) {
                    $result = $game->getEvent()->getNbPointsForRightSimpleBet();
                    if(($pronostic->getSliceScore())
                    && (($game->getScoreTeam1() > 0) || ($game->getScoreTeam2() > 0))
                    && (($game->getScoreDifference() >= $pronostic->getSliceScore()->getMin()) 
                         && ($game->getScoreDifference() <= $pronostic->getSliceScore()->getMax())))
                    {
                        $result += $game->getEvent()->getNbPointsForRightSliceScore();
                    }
                }
                else $result = $game->getEvent()->getNbPointsForWrongSimpleBet();
                
            }
            // pronostic avec score
            else {
                // match avec prolongations
                if($game->hasOvertime()) {
                    //score exact en prolongations
                    if(($game->getScoreTeam1Overtime() == $pronostic->getScoreTeam1Overtime()) 
                    && ($game->getScoreTeam2Overtime() == $pronostic->getScoreTeam2Overtime())
                    && $pronostic->getOvertime())
                    {
                        $result = 2;
                    }
                    //equipe gagnante avant les 120 minutes
                    if($game->getWhoWinAfterOvertime() > 0) {
                       //pronostic correct mais pas bon score
                       if($pronostic->getOvertime() && ($game->getWhoWinAfterOvertime() == $pronostic->getWhoWinAfterOvertime())) {
                           $result += $game->getEvent()->getNbPointsForAlmostRightBet();
                       }
                        // TAB ?
                        //if(($game->getWinner() == $pronostic->getWinner()) && $pronostic->getOvertime()) {
                            //$result += 1;
                        //}
                        // victoire d'une equipe apres 120 min mais pronostic TAB pour bonne equipe
                        if($pronostic->getOvertime()
                              && ($game->getWhoWinAfterOvertime() == 1)
                              && ($pronostic->getWhoWinAfterOvertime() == 0)
                              && ($pronostic->getWinner() == $game->getTeam1())
                        )
                        {
                            $result += $game->getEvent()->getNbPointsForAlmostRightBet();
                        }
                        elseif($pronostic->getOvertime()
                              && ($game->getWhoWinAfterOvertime() == 2)
                              && ($pronostic->getWhoWinAfterOvertime() == 0)
                              && ($pronostic->getWinner() == $game->getTeam2())
                        )
                        {
                            $result += $game->getEvent()->getNbPointsForAlmostRightBet();
                        }
                    }
                    // match nul apres 120 min et vainqueur apres TAB exact
                    elseif($pronostic->getOvertime() 
                            && ($game->getWhoWinAfterOvertime() == $pronostic->getWhoWinAfterOvertime())
                            && ($pronostic->getWinner() == $game->getWinner())
                        )
                    {
                        $result += $game->getEvent()->getNbPointsForAlmostRightBet();
                    }

                    // pas le bon score au bout des 90 min
                    //if($pronostic->getWhoWin() != $game->getWhoWin()) {
                        //equipe gagnante apres TAB egale a equipe du pronostic 90 min
                        if(($game->getWinner() == $game->getTeam1()) && ($pronostic->getWhoWin() == 1))
                            $result += $game->getEvent()->getNbPointsForAlmostRightBet();
                        elseif(($game->getWinner() == $game->getTeam2()) && ($pronostic->getWhoWin() == 2))
                            $result += $game->getEvent()->getNbPointsForAlmostRightBet();
                        //equipe gagnante apres TAB egale a equipe du pronostic 120 min
                        elseif(($game->getWinner() == $game->getTeam1()) && ($pronostic->getWhoWinAfterOvertime() == 1))
                        	$result += $game->getEvent()->getNbPointsForAlmostRightBet();
                        elseif(($game->getWinner() == $game->getTeam2()) && ($pronostic->getWhoWinAfterOvertime() == 2))
                            $result += $game->getEvent()->getNbPointsForAlmostRightBet();
                        //equipe gagnante apres prolongations sans TAB egale a equipe du pronostic 90 min
                        elseif(($game->getWhoWinAfterOvertime() == 1) && ($pronostic->getWhoWin() == 1))
                            $result += $game->getEvent()->getNbPointsForAlmostRightBet();
                        elseif(($game->getWhoWinAfterOvertime() == 2) && ($pronostic->getWhoWin() == 2))
                            $result += $game->getEvent()->getNbPointsForAlmostRightBet();
                    //}
                }
                // pas de prolongation mais pronostic avec
                elseif($pronostic->getOvertime()) {
                    if(($pronostic->getWhoWinAfterOvertime() == 1) && ($game->getWhoWin() == 1))
                        $result += $game->getEvent()->getNbPointsForAlmostRightBet();
                    elseif(($pronostic->getWhoWinAfterOvertime() == 2) && ($game->getWhoWin() == 2))
                        $result += $game->getEvent()->getNbPointsForAlmostRightBet();
                    elseif(($pronostic->getWhoWinAfterOvertime() == 1) && ($pronostic->getWinner() == $game->getTeam1()) && ($game->getWhoWin() == 1))
                        $result += $game->getEvent()->getNbPointsForAlmostRightBet();
                    elseif(($pronostic->getWhoWinAfterOvertime() == 2) && ($pronostic->getWinner() == $game->getTeam2()) && ($game->getWhoWin() == 2))
                        $result += $game->getEvent()->getNbPointsForAlmostRightBet();
                    elseif(($pronostic->getWhoWinAfterOvertime() == 0) && ($pronostic->getWinner() == $game->getTeam1()) && ($game->getWhoWin() == 1))
                        $result += $game->getEvent()->getNbPointsForAlmostRightBet();
                    elseif(($pronostic->getWhoWinAfterOvertime() == 0) && ($pronostic->getWinner() == $game->getTeam2()) && ($game->getWhoWin() == 2))
                        $result += $game->getEvent()->getNbPointsForAlmostRightBet();

                }
                // score exact au bout de 90 min
                if(($game->getScoreTeam1() == $pronostic->getScoreTeam1()) && ($game->getScoreTeam2() == $pronostic->getScoreTeam2())) {
                    $result += $game->getEvent()->getNbPointsForRightBetWithScore();
                }
                // bonne equipe au bout de 90 min
                elseif($game->getWhoWin() == $pronostic->getWhoWin()) {
                    $result += $game->getEvent()->getNbPointsForRightBet();
                }
                else $result += $game->getEvent()->getNbPointsForWrongBet();
            }
            $pronostic->setResult($result);
            $this->em->persist($pronostic);
            $this->em->flush();

           $standing = $this->em->getRepository('DwfPronosticsBundle:Standing')->findByUserAndContestAndGame($pronostic->getUser(), $pronostic->getContest(), $game);
           if($standing) {
               // on supprime le standing eventuel sur le meme game pour pouvoir le recreer par la suite
               $standing = $standing[0];
               $this->em->remove($standing);
               $this->em->flush();
           }
           $lastStanding = $this->em->getRepository('DwfPronosticsBundle:Standing')->getMaxPointsByUserAndContestBeforeGame($pronostic->getUser(), $pronostic->getContest(), $game);
           if($lastStanding) {
               // recuperation du nombre de points et nombre de pronostics du dernier standing enregistré
               $lastStandingPoints = $lastStanding[0]->getPoints();
               $lastStandingPronostics = $lastStanding[0]->getPronostics();
           }
           else {
               $lastStandingPoints = 0;
               $lastStandingPronostics = 0;
           }
           $standing = new Standing();
           $standing->setUser($pronostic->getUser());
           $standing->setEvent($pronostic->getEvent());
           $standing->setContest($pronostic->getContest());
           $standing->setPoints($lastStandingPoints + $result);
           $standing->setPronostics($lastStandingPronostics + 1);
           $standing->setGame($game);
           $this->em->persist($standing);
           $this->em->flush();
        }
    }
    
    public function setResultsForGroup(\Dwf\PronosticsBundle\Entity\Game $game)
    {
        //$results = $this->em->getRepository('DwfPronosticsBundle:GameTypeResult')->getResultsByGame($game);
        $results = $this->em->getRepository('DwfPronosticsBundle:GameTypeResult')->findByGame($game);
        //var_dump($results);exit();
        if($results) {
            // on supprime les results eventuels sur le meme game pour pouvoir les recreer par la suite
            foreach ($results as $result) {
                $this->em->remove($result);
                $this->em->flush();
            }
        }
        
        //var_dump($results); exit();
        //if(!$results) {
            $nbPointsTeam1 = 0;
            $nbPointsTeam2 = 0;
            $goalaverageTeam1 = 0;
            $goalaverageTeam2 = 0;
            if($game->getEvent()->getChampionship()) {
                if($game->getType()->getPosition() > 1) {
                    $gameType = $this->em->getRepository('DwfPronosticsBundle:GameType')->getByEventAndPosition($game->getEvent(), $game->getType()->getPosition() - 1);
                    $lastResultsTeam1 = $this->em->getRepository('DwfPronosticsBundle:GameTypeResult')->getResultsByGameTypeAndEventAndTeam($gameType, $game->getEvent(), $game->getTeam1());
                    if($lastResultsTeam1) {
                        $nbPointsTeam1 = $lastResultsTeam1[0]["total"];
                        $goalaverageTeam1 = $lastResultsTeam1[0]["goals"];
                    }
                    $lastResultsTeam2 = $this->em->getRepository('DwfPronosticsBundle:GameTypeResult')->getResultsByGameTypeAndEventAndTeam($gameType, $game->getEvent(), $game->getTeam2());
                    if($lastResultsTeam2) {
                        $nbPointsTeam2 = $lastResultsTeam2[0]["total"];
                        $goalaverageTeam2 = $lastResultsTeam2[0]["goals"];
                    }
                }
            }
            
            $result = new GameTypeResult();
            $result->setEvent($game->getEvent());
            $result->setGameType($game->getType());
            $result->setTeam($game->getTeam1());
            $result->setGame($game);
            if($game->getWhoWin() == 1)
                $nbPoints = $game->getEvent()->getNbPointsForWin();
            elseif($game->getWhoWin() == 0)
                $nbPoints = $game->getEvent()->getNbPointsForDraw();
            else $nbPoints = $game->getEvent()->getNbPointsForLoss();
            
            $result->setResult($nbPoints + $nbPointsTeam1);
            $result->setGoalaverage(($game->getScoreTeam1() - $game->getScoreTeam2()) + $goalaverageTeam1);
            
            $this->em->persist($result);
            $this->em->flush();
            
            $result = new GameTypeResult();
            $result->setEvent($game->getEvent());
            $result->setGameType($game->getType());
            $result->setTeam($game->getTeam2());
            $result->setGame($game);
            if($game->getWhoWin() == 2)
                $nbPoints = $game->getEvent()->getNbPointsForWin();
            elseif($game->getWhoWin() == 0)
                $nbPoints = $game->getEvent()->getNbPointsForDraw();
            else $nbPoints = $game->getEvent()->getNbPointsForLoss();
            $result->setResult($nbPoints + $nbPointsTeam2);
            $result->setGoalaverage(($game->getScoreTeam2() - $game->getScoreTeam1()) + $goalaverageTeam2);
            
            $this->em->persist($result);
            $this->em->flush();
        //}
    
    }
}