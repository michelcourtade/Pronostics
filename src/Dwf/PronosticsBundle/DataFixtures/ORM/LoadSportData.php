<?php
namespace Dwf\PronosticsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Dwf\PronosticsBundle\Entity\Sport;

class LoadSportData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach (array('Football', 'Rugby', 'Football Américain', 'Tennis') as $sportName) {
            $sport = new Sport();
            $sport->setName($sportName);
            $manager->persist($sport);
            $manager->flush();
        }
    }

    /**
     * @return int
     */
    public function getOrder()
    {
     	return 1;
    }
}