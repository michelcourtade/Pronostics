<?php

namespace Dwf\PronosticsBundle\Championship;
use Dwf;

class ChampionshipManager {
    
    protected $em;
    protected $event;
    
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }
    
    public function setEvent(Dwf\PronosticsBundle\Entity\Event $event)
    {
        $this->event = $event;
    }

    public function getCurrentChampionshipDay()
    {
        if($this->event->getChampionship()) {
            $lastGamePlayed = $this->em->getRepository('DwfPronosticsBundle:Game')->findLastGamePlayedByEvent($this->event);
            if(count($lastGamePlayed) > 0) {
                $lastGamePlayed = $lastGamePlayed[0];
                $gamesLeftInChampionshipDay = $this->em->getRepository('DwfPronosticsBundle:Game')->findGamesLeftByEventAndGameType($this->event, $lastGamePlayed->getType());
                if($gamesLeftInChampionshipDay)
                    $currentChampionshipDay = $this->em->getRepository('DwfPronosticsBundle:GameType')->find($lastGamePlayed->getType());
                else {
                    $currentChampionshipDay = $this->em->getRepository('DwfPronosticsBundle:GameType')->getByEventAndPosition($this->event, $lastGamePlayed->getType()->getPosition() + 1);
                    if($currentChampionshipDay)
                        $currentChampionshipDay = $currentChampionshipDay[0];
                    else $currentChampionshipDay = '';
                }
            }
            else $currentChampionshipDay = '';
        }
        else $currentChampionshipDay = '';

        return $currentChampionshipDay;
    }
}