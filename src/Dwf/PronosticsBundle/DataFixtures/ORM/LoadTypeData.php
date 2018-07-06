<?php
namespace Dwf\PronosticsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Dwf\PronosticsBundle\Entity\GameType;
use Cocur\Slugify\Slugify;

class LoadTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $types = array(
                'Groupe A'              => array("UEFA Euro 2016"),
                'Groupe B'              => array("UEFA Euro 2016"),
                'Groupe C'              => array("UEFA Euro 2016"),
                'Groupe D'              => array("UEFA Euro 2016"),
                'Groupe E'              => array("UEFA Euro 2016"),
                'Groupe F'              => array("UEFA Euro 2016"),
                'Huitième de Finale'    => array("UEFA Euro 2016"),
                'Quart de Finale'       => array("UEFA Euro 2016"),
                'Demi Finale'           => array("UEFA Euro 2016"),
                'Finale'                => array("UEFA Euro 2016"),
                'Week 1'                => array("Championnat NFL 2016"),
                'Week 2'                => array("Championnat NFL 2016"),
                'Week 3'                => array("Championnat NFL 2016"),
                'Week 4'                => array("Championnat NFL 2016"),
                'Week 5'                => array("Championnat NFL 2016"),
                'Week 6'                => array("Championnat NFL 2016"),
                'Week 7'                => array("Championnat NFL 2016"),
                'Week 8'                => array("Championnat NFL 2016"),
                'Week 9'                => array("Championnat NFL 2016"),
                'Week 10'               => array("Championnat NFL 2016"),
                'Week 11'               => array("Championnat NFL 2016"),
                'Week 12'               => array("Championnat NFL 2016"),
                'Week 13'               => array("Championnat NFL 2016"),
                'Week 14'               => array("Championnat NFL 2016"),
                'Week 15'               => array("Championnat NFL 2016"),
                'Week 16'               => array("Championnat NFL 2016"),
                'Week 17'               => array("Championnat NFL 2016"),
        );
        foreach ($types as $typeName => $events) {
            
            $gameType = new GameType();
            
            $gameType->setName($typeName);
            $gameType->setCanHaveOvertime(false);
            foreach ($events as $event) {
                $gameType->addEvent($this->getReference($event));
            }
            
            $manager->persist($gameType);
            $manager->flush();
            
            $this->addReference($typeName, $gameType);
        }
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 4;
    }
}