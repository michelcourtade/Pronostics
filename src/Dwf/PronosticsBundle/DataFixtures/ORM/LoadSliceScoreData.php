<?php
namespace Dwf\PronosticsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Dwf\PronosticsBundle\Entity\SliceScore;
use Cocur\Slugify\Slugify;

class LoadSliceScoreData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $slices = array(
                '1 - 5'                 => array('min' => 1, 'max' => 5, 'sports' => array("Football Américain")),
                '6 - 10'                => array('min' => 6, 'max' => 10, 'sports' => array("Football Américain")),
                '11 - 15'               => array('min' => 11, 'max' => 15, 'sports' => array("Football Américain")),
                '16 - 20'               => array('min' => 16, 'max' => 20, 'sports' => array("Football Américain")),
        );
        foreach ($slices as $sliceName => $values) {
            
            $sliceScore = new SliceScore();
            
            $sliceScore->setName($sliceName);
            $sliceScore->setMin($values['min']);
            $sliceScore->setMax($values['max']);
            foreach ($values['sports'] as $sport) {
                $sliceScore->addSport($this->getReference($sport));
            }
            
            $manager->persist($sliceScore);
            $manager->flush();
            
            $this->addReference($sliceName, $sliceScore);
        }
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 7;
    }
}