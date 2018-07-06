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
        $date = new \DateTime("2016-6-10 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe A"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Albanie"));
        $game->setTeam2($this->getReference("Suisse"));
        $date = new \DateTime("2016-6-11 15:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe A"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Pays de Galles"));
        $game->setTeam2($this->getReference("Slovaquie"));
        $date = new \DateTime("2016-6-11 18:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe B"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Angleterre"));
        $game->setTeam2($this->getReference("Russie"));
        $date = new \DateTime("2016-6-11 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe B"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Turquie"));
        $game->setTeam2($this->getReference("Croatie"));
        $date = new \DateTime("2016-6-12 15:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe D"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Pologne"));
        $game->setTeam2($this->getReference("Irlande du nord"));
        $date = new \DateTime("2016-6-12 18:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe C"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Allemagne"));
        $game->setTeam2($this->getReference("Ukraine"));
        $date = new \DateTime("2016-6-12 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe B"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Espagne"));
        $game->setTeam2($this->getReference("République Tchèque"));
        $date = new \DateTime("2016-6-13 15:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe D"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Irlande"));
        $game->setTeam2($this->getReference("Suède"));
        $date = new \DateTime("2016-6-13 18:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe E"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Belgique"));
        $game->setTeam2($this->getReference("Italie"));
        $date = new \DateTime("2016-6-13 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe B"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Autriche"));
        $game->setTeam2($this->getReference("Hongrie"));
        $date = new \DateTime("2016-6-14 18:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe F"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Portugal"));
        $game->setTeam2($this->getReference("Islande"));
        $date = new \DateTime("2016-6-14 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe F"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Russie"));
        $game->setTeam2($this->getReference("Slovaquie"));
        $date = new \DateTime("2016-6-15 15:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe B"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Roumanie"));
        $game->setTeam2($this->getReference("Suisse"));
        $date = new \DateTime("2016-6-15 18:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe A"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("France"));
        $game->setTeam2($this->getReference("Albanie"));
        $date = new \DateTime("2016-6-15 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe A"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Angleterre"));
        $game->setTeam2($this->getReference("Pays de Galles"));
        $date = new \DateTime("2016-6-16 15:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe B"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);

        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Ukraine"));
        $game->setTeam2($this->getReference("Irlande du nord"));
        $date = new \DateTime("2016-6-16 18:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe C"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Allemagne"));
        $game->setTeam2($this->getReference("Pologne"));
        $date = new \DateTime("2016-6-16 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe C"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Italie"));
        $game->setTeam2($this->getReference("Suède"));
        $date = new \DateTime("2016-6-17 15:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe E"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("République Tchèque"));
        $game->setTeam2($this->getReference("Croatie"));
        $date = new \DateTime("2016-6-17 18:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe D"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Espagne"));
        $game->setTeam2($this->getReference("Turquie"));
        $date = new \DateTime("2016-6-17 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe D"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Belgique"));
        $game->setTeam2($this->getReference("Irlande"));
        $date = new \DateTime("2016-6-18 15:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe E"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Islande"));
        $game->setTeam2($this->getReference("Hongrie"));
        $date = new \DateTime("2016-6-18 18:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe F"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Portugal"));
        $game->setTeam2($this->getReference("Autriche"));
        $date = new \DateTime("2016-6-18 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe F"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Roumanie"));
        $game->setTeam2($this->getReference("Albanie"));
        $date = new \DateTime("2016-6-19 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe A"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Suisse"));
        $game->setTeam2($this->getReference("France"));
        $date = new \DateTime("2016-6-19 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe A"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Russie"));
        $game->setTeam2($this->getReference("Pays de Galles"));
        $date = new \DateTime("2016-6-20 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe B"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Slovaquie"));
        $game->setTeam2($this->getReference("Angleterre"));
        $date = new \DateTime("2016-6-20 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe B"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Ukraine"));
        $game->setTeam2($this->getReference("Pologne"));
        $date = new \DateTime("2016-6-21 18:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe C"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Irlande du nord"));
        $game->setTeam2($this->getReference("Allemagne"));
        $date = new \DateTime("2016-6-21 18:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe C"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("République Tchèque"));
        $game->setTeam2($this->getReference("Turquie"));
        $date = new \DateTime("2016-6-21 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe D"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Croatie"));
        $game->setTeam2($this->getReference("Espagne"));
        $date = new \DateTime("2016-6-21 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe D"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Islande"));
        $game->setTeam2($this->getReference("Autriche"));
        $date = new \DateTime("2016-6-22 18:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe F"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Hongrie"));
        $game->setTeam2($this->getReference("Portugal"));
        $date = new \DateTime("2016-6-22 18:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe F"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Italie"));
        $game->setTeam2($this->getReference("Irlande"));
        $date = new \DateTime("2016-6-22 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe E"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("UEFA Euro 2016"));
        $game->setTeam1($this->getReference("Suède"));
        $game->setTeam2($this->getReference("Belgique"));
        $date = new \DateTime("2016-6-22 21:00");
        $game->setDate($date);
        $game->setType($this->getReference("Groupe E"));
        $manager->persist($game);
        $manager->flush();
        $this->addReference($game->__toString(), $game);
        
        $game = new Game();
        $game->setEvent($this->getReference("Championnat NFL 2016"));
        $game->setTeam1($this->getReference("Carolina Panthers"));
        $game->setTeam2($this->getReference("Denver Broncos"));
        $date = new \DateTime("2016-9-8 21:30");
        $game->setDate($date);
        $game->setType($this->getReference("Week 1"));
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