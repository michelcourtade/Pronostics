<?php
namespace Dwf\PronosticsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Dwf\PronosticsBundle\Entity\Event;
use Cocur\Slugify\Slugify;

class LoadEventData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $event = new Event();
        $event->setName("UEFA Euro 2016");
        
        $slugify = new Slugify();
        $sportSlug = $slugify->slugify("Football");
        $event->setSport($this->getReference($sportSlug));
        
        $slugify = new Slugify();
        $nameSlug = $slugify->slugify("UEFA Euro 2016");
        $event->setPath($nameSlug.'.png');
        
        $event->setNationalTeams(true);
        $event->setChampionship(false);
        
        $startDate = new \DateTime("now");
        $event->setStartDate($startDate);
        
        $finishDate = $startDate->modify("+1 month");
        $event->setFinishDate($finishDate);

        $event->setNbPointsForLoss(0);
        $event->setNbPointsForDraw(1);
        $event->setNbPointsForWin(3);
        
        $event->setActive(true);
        
        $event->setSimpleBet(true);
        $event->setScoreDiff(false);
        $event->setNbPointsForRightSimpleBet(3);
        $event->setNbPointsForWrongSimpleBet(1);
        $event->setNbPointsForRightSliceScore(2);
        
        $event->setNbPointsForRightBet(3);
        $event->setNbPointsForRightBetWithScore(5);
        $event->setNbPointsForWrongBet(1);
        $event->setNbPointsForAlmostRightBet(1);
        
        $manager->persist($event);
        $manager->flush();
        
        $this->addReference("UEFA Euro 2016", $event);
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 3;
    }
}