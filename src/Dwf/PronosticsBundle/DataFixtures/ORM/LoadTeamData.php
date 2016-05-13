<?php
namespace Dwf\PronosticsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Dwf\PronosticsBundle\Entity\Team;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Cocur\Slugify\Slugify;

class LoadTeamData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $teams = array(
                'France' => array('iso' =>'FRA', 'national' => '1', 'sport' => 'Football'),
                'Angleterre' => array('iso' =>'GBR', 'national' => '1', 'sport' => 'Football Américain'),

        );
        foreach ($teams as $name => $infos) {
            $team = new Team();
            $team->setName($name);
            $team->setIso($infos['iso']);
            $team->setNational($infos['national']);
            $slug = new Slugify();
            $team->setSport($this->getReference($slug->slugify($infos['sport'])));
//             var_dump($team->getFixturesPath() . '01-copy.png');
//             copy($team->getFixturesPath() . '01.png', $team->getFixturesPath() . '01-copy.png');
//             $file = new UploadedFile($team->getFixturesPath() . '01-copy.png', 'Image1', null, null, null, true);
            $slugify = new Slugify();
            $sport = $slugify->slugify($team->getSport());
            $team->setPath($sport.'-'.($team->getNational() ? 'NAT-' : 'CLU-').$team->getIso().'.png');
            //$team->path = sha1(uniqid(mt_rand(), true)).'.'.$team->file->guessExtension();
            $team->setFile($team->getAbsolutePath());
            $manager->persist($team);
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