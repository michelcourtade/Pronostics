<?php
namespace Dwf\PronosticsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Dwf\PronosticsBundle\Entity\Player;
use Cocur\Slugify\Slugify;

class LoadPlayerData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        
            $player = new Player();
            $player->setFirstname("Christiano");
            $player->setName("Ronaldo");
            $player->setNationalTeam($this->getReference("Portugal"));
            $player->setTeam($this->getReference("Real Madrid"));
            $player->setActive(true);
            $manager->persist($player);
            $manager->flush();
            $this->addReference($player->__toString(), $player);
        
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 6;
    }
}