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
                'France'                => array('iso' => 'FRA', 'national' => '1', 'sport' => 'Football'),
                'Angleterre'            => array('iso' => 'GBR', 'national' => '1', 'sport' => 'Football'),
                'Allemagne'             => array('iso' => 'GER', 'national' => '1', 'sport' => 'Football'),
                'Espagne'               => array('iso' => 'ESP', 'national' => '1', 'sport' => 'Football'),
                'Brésil'                => array('iso' => 'BRA', 'national' => '1', 'sport' => 'Football'),
                'Croatie'               => array('iso' => 'CRO', 'national' => '1', 'sport' => 'Football'),
                'Pays-Bas'              => array('iso' => 'NED', 'national' => '1', 'sport' => 'Football'),
                'Italie'                => array('iso' => 'ITA', 'national' => '1', 'sport' => 'Football'),
                'Suisse'                => array('iso' => 'SUI', 'national' => '1', 'sport' => 'Football'),
                'Portugal'              => array('iso' => 'PRO', 'national' => '1', 'sport' => 'Football'),
                'Argentine'             => array('iso' => 'ARG', 'national' => '1', 'sport' => 'Football'),
                'Norvège'               => array('iso' => 'NOR', 'national' => '1', 'sport' => 'Football'),
                'Paraguay'              => array('iso' => 'PAR', 'national' => '1', 'sport' => 'Football'),
                'Jamaïque'              => array('iso' => 'JAM', 'national' => '1', 'sport' => 'Football'),
                'Ghana'                 => array('iso' => 'GHA', 'national' => '1', 'sport' => 'Football'),
                'USA'                   => array('iso' => 'USA', 'national' => '1', 'sport' => 'Football'),
                'Belgique'              => array('iso' => 'BEL', 'national' => '1', 'sport' => 'Football'),
                'Algérie'               => array('iso' => 'ALG', 'national' => '1', 'sport' => 'Football'),
                'Russie'                => array('iso' => 'RUS', 'national' => '1', 'sport' => 'Football'),
                'République de Corée'   => array('iso' => 'KOR', 'national' => '1', 'sport' => 'Football'),
                'Mexique'               => array('iso' => 'MEX', 'national' => '1', 'sport' => 'Football'),
                'Cameroun'              => array('iso' => 'CMR', 'national' => '1', 'sport' => 'Football'),
                'Chili'                 => array('iso' => 'CHI', 'national' => '1', 'sport' => 'Football'),
                'Australie'             => array('iso' => 'AUS', 'national' => '1', 'sport' => 'Football'),
                'Grèce'                 => array('iso' => 'GRE', 'national' => '1', 'sport' => 'Football'),
                'Japon'                 => array('iso' => 'JPN', 'national' => '1', 'sport' => 'Football'),
                'Uruguay'               => array('iso' => 'URU', 'national' => '1', 'sport' => 'Football'),
                'Costa Rica'            => array('iso' => 'CRC', 'national' => '1', 'sport' => 'Football'),
                'Colombie'              => array('iso' => 'COL', 'national' => '1', 'sport' => 'Football'),
                'Côte d\'Ivoire'        => array('iso' => 'CIV', 'national' => '1', 'sport' => 'Football'),
                'Equateur'              => array('iso' => 'ECU', 'national' => '1', 'sport' => 'Football'),
                'Honduras'              => array('iso' => 'HON', 'national' => '1', 'sport' => 'Football'),
                'Bosnie-et-Herzégovine' => array('iso' => 'BIH', 'national' => '1', 'sport' => 'Football'),
                'Nigeria'               => array('iso' => 'NGA', 'national' => '1', 'sport' => 'Football'),
                'Iran'                  => array('iso' => 'IRN', 'national' => '1', 'sport' => 'Football'),
                'Ecosse'                => array('iso' => 'SCO', 'national' => '1', 'sport' => 'Football'),
                'Canada'                => array('iso' => 'CAN', 'national' => '1', 'sport' => 'Football'),
                'Roumanie'              => array('iso' => 'ROM', 'national' => '1', 'sport' => 'Football'),
                

        );
        foreach ($teams as $name => $infos) {
            $team = new Team();
            $team->setName($name);
            $team->setIso($infos['iso']);
            $team->setNational($infos['national']);
            $slug = new Slugify();
            $team->setSport($this->getReference($slug->slugify($infos['sport'])));
            $slugify = new Slugify();
            $sport = $slugify->slugify($team->getSport());
            $team->setPath($sport.'-'.($team->getNational() ? 'NAT-' : 'CLU-').$team->getIso().'.png');
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