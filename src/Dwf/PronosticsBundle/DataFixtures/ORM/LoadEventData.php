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
        $event->setSport($this->getReference("Football"));
        $slugify = new Slugify();
        $nameSlug = $slugify->slugify("UEFA Euro 2016");
        $event->setPath($nameSlug.'.png');
        $event->setNationalTeams(true);
        $event->setChampionship(false);
        $startDate = new \DateTime("2016-05-10 20:00");
        $event->setStartDate($startDate);
        $finishDate = $startDate->modify("2016-07-11 23:00");
        $event->setFinishDate($finishDate);
        $event->setNbPointsForLoss(0);
        $event->setNbPointsForDraw(1);
        $event->setNbPointsForWin(3);
        $event->setActive(true);

        $event->setSimpleBet(false);
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

        $event = new Event();
        $event->setName("Rolland Garros 2016");
        $event->setSport($this->getReference("Tennis"));
        $slugify = new Slugify();
        $nameSlug = $slugify->slugify("Rolland Garros 2016");
        $event->setPath($nameSlug.'.png');
        $event->setNationalTeams(false);
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

        $this->addReference("Rolland Garros 2016", $event);

    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 3;
    }
}