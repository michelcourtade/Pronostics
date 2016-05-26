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
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Angleterre"));
        $game->setTeam2($this->getReference("Russie"));
        $date = new \DateTime("11/06/2016 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe B"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Turquie"));
        $game->setTeam2($this->getReference("Croatie"));
        $date = new \DateTime("12/06/2016 15:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe D"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Pologne"));
        $game->setTeam2($this->getReference("Irlande du nord"));
        $date = new \DateTime("12/06/2016 18:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe C"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Allemagne"));
        $game->setTeam2($this->getReference("Ukraine"));
        $date = new \DateTime("12/06/2016 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe B"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Espagne"));
        $game->setTeam2($this->getReference("République Tchèque"));
        $date = new \DateTime("13/06/2016 15:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe D"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Irlande"));
        $game->setTeam2($this->getReference("Suède"));
        $date = new \DateTime("13/06/2016 18:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe E"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Belgique"));
        $game->setTeam2($this->getReference("Italie"));
        $date = new \DateTime("13/06/2016 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe B"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Autriche"));
        $game->setTeam2($this->getReference("Hongrie"));
        $date = new \DateTime("14/06/2016 18:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe F"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Portugal"));
        $game->setTeam2($this->getReference("Islande"));
        $date = new \DateTime("14/06/2016 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe F"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Russie"));
        $game->setTeam2($this->getReference("Slovaquie"));
        $date = new \DateTime("15/06/2016 15:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe B"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Roumanie"));
        $game->setTeam2($this->getReference("Suisse"));
        $date = new \DateTime("15/06/2016 18:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe A"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("France"));
        $game->setTeam2($this->getReference("Albanie"));
        $date = new \DateTime("15/06/2016 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe A"));
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