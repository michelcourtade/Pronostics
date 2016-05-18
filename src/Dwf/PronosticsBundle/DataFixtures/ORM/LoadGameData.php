<?php
namespace Dwf\PronosticsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Dwf\PronosticsBundle\Entity\Game;
use Cocur\Slugify\Slugify;

class LoadGameData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        
        $game = new Game();
        
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        
        $game->setTeam1($this->getReference("France"));
        $game->setTeam2($this->getReference("Roumanie"));
        
        $date = new \DateTime("10/06/2016 21:00");
        $game->setDate($date);
        
        $game->setType($this->getReference("Groupe A"));
        
        $manager->persist($game);
        $manager->flush();
        
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        
        $game->setTeam1($this->getReference("Albanie"));
        $game->setTeam2($this->getReference("Suisse"));
        
        $date = new \DateTime("11/06/2016 15:00");
        $game->setDate($date);
        
        $game->setType($this->getReference("Groupe A"));
        
        $manager->persist($game);
        $manager->flush();
        
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        
        $game->setTeam1($this->getReference("Pays de Galles"));
        $game->setTeam2($this->getReference("Slovaquie"));
        
        $date = new \DateTime("11/06/2016 18:00");
        $game->setDate($date);
        
        $game->setType($this->getReference("Groupe B"));
        
        $manager->persist($game);
        $manager->flush();
        
        $this->addReference($game->__toString(), $game);
        
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 5;
    }
}