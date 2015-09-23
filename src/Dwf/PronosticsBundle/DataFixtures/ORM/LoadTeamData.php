<?php
namespace Dwf\PronosticsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Dwf\PronosticsBundle\Entity\Team;

class LoadTeamData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $teams = array(
                'France' => array('iso' =>'FRA', 'national' => '1'),
                'Angleterre' => array('iso' =>'GBR', 'national' => '1'),

        )
        foreach ($teams as $name => $infos) {
            $team = new Team();
            $team->setName($$name);
            $team->setIso($infos['iso']);
            $team->setNational($infos['national']);
            $manager->persist($sport);
            $manager->flush();
        }
    }

    /**
     * @return int
     */
    public function getOrder()
    {
     	return 2;
    }
}