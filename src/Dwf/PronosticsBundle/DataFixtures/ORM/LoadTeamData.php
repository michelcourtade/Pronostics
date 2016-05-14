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
                'Albanie'               => array('iso' => 'ALB', 'national' => '1', 'sport' => 'Football'),
                'Pays de Galles'        => array('iso' => 'WAL', 'national' => '1', 'sport' => 'Football'),
                'Slovaquie'             => array('iso' => 'SVK', 'national' => '1', 'sport' => 'Football'),
                'Turquie'               => array('iso' => 'TUR', 'national' => '1', 'sport' => 'Football'),
                'Pologne'               => array('iso' => 'POL', 'national' => '1', 'sport' => 'Football'),
                'Irlande du nord'       => array('iso' => 'NIR', 'national' => '1', 'sport' => 'Football'),
                'Ukraine'               => array('iso' => 'UKR', 'national' => '1', 'sport' => 'Football'),
                'République Tchèque'    => array('iso' => 'CZE', 'national' => '1', 'sport' => 'Football'),
                'Irlande'               => array('iso' => 'IRL', 'national' => '1', 'sport' => 'Football'),
                'Suède'                 => array('iso' => 'SWE', 'national' => '1', 'sport' => 'Football'),
                'Autriche'              => array('iso' => 'AUT', 'national' => '1', 'sport' => 'Football'),
                'Hongrie'               => array('iso' => 'HUN', 'national' => '1', 'sport' => 'Football'),
                'Islande'               => array('iso' => 'ISL', 'national' => '1', 'sport' => 'Football'),
                
                // Ligue 1 Teams
                'AS Monaco FC'              => array('iso' => 'ASM', 'national' => '0', 'sport' => 'Football'),
                'AS Saint-Etienne'          => array('iso' => 'ASS', 'national' => '0', 'sport' => 'Football'),
                'EA Guingamp'               => array('iso' => 'EAG', 'national' => '0', 'sport' => 'Football'),
                'Evian TG FC'               => array('iso' => 'ETG', 'national' => '0', 'sport' => 'Football'),
                'FC Lorient'                => array('iso' => 'FCL', 'national' => '0', 'sport' => 'Football'),
                'FC Metz'                   => array('iso' => 'FCM', 'national' => '0', 'sport' => 'Football'),
                'FC Nantes'                 => array('iso' => 'FCN', 'national' => '0', 'sport' => 'Football'),
                'Girondins de Bordeaux'     => array('iso' => 'BOR', 'national' => '0', 'sport' => 'Football'),
                'LOSC Lille'                => array('iso' => 'LOS', 'national' => '0', 'sport' => 'Football'),
                'Montpellier Hérault SC'    => array('iso' => 'MHS', 'national' => '0', 'sport' => 'Football'),
                'OGC Nice'                  => array('iso' => 'OGC', 'national' => '0', 'sport' => 'Football'),
                'Olympique de Marseille'    => array('iso' => 'OM', 'national' => '0', 'sport' => 'Football'),
                'Olympique Lyonnais'        => array('iso' => 'OL', 'national' => '0', 'sport' => 'Football'),
                'Paris Saint Germain'       => array('iso' => 'PSG', 'national' => '0', 'sport' => 'Football'),
                'RC Lens'                   => array('iso' => 'RCL', 'national' => '0', 'sport' => 'Football'),
                'SC Bastia'                 => array('iso' => 'SCB', 'national' => '0', 'sport' => 'Football'),
                'SM Caen'                   => array('iso' => 'SMC', 'national' => '0', 'sport' => 'Football'),
                'Stade de Reims'            => array('iso' => 'SR', 'national' => '0', 'sport' => 'Football'),
                'Stade Rennais FC'          => array('iso' => 'SRF', 'national' => '0', 'sport' => 'Football'),
                'Toulouse FC'               => array('iso' => 'TFC', 'national' => '0', 'sport' => 'Football'),
                
                // LDC Teams
                'Galatasaray'               => array('iso' => 'GLT', 'national' => '0', 'sport' => 'Football'),
                'Liverpool'                 => array('iso' => 'LVP', 'national' => '0', 'sport' => 'Football'),
                'Ludogorets'                => array('iso' => 'LGT', 'national' => '0', 'sport' => 'Football'),
                'Real Madrid'               => array('iso' => 'RMA', 'national' => '0', 'sport' => 'Football'),
                'Arsenal'                   => array('iso' => 'ASN', 'national' => '0', 'sport' => 'Football'),
                'Anderlecht'                => array('iso' => 'AND', 'national' => '0', 'sport' => 'Football'),
                'Borussia Dortmund'         => array('iso' => 'BDM', 'national' => '0', 'sport' => 'Football'),
                'Benfica'                   => array('iso' => 'BEN', 'national' => '0', 'sport' => 'Football'),
                'FC Porto'                  => array('iso' => 'FCP', 'national' => '0', 'sport' => 'Football'),
                'Bayern Munich'             => array('iso' => 'BYM', 'national' => '0', 'sport' => 'Football'),
                'FC Barcelone'              => array('iso' => 'FCB', 'national' => '0', 'sport' => 'Football'),
                'Ajax Amsterdam'            => array('iso' => 'AJA', 'national' => '0', 'sport' => 'Football'),
                'Shalke 04'                 => array('iso' => 'SLK', 'national' => '0', 'sport' => 'Football'),
                'NK Maribor'                => array('iso' => 'NKM', 'national' => '0', 'sport' => 'Football'),
                'AS Roma'                   => array('iso' => 'ASR', 'national' => '0', 'sport' => 'Football'),
                'Chelsea FC'                => array('iso' => 'CHE', 'national' => '0', 'sport' => 'Football'),
                'Athletic Club Bilbao'      => array('iso' => 'BIL', 'national' => '0', 'sport' => 'Football'),
                'CSKA Moscou'               => array('iso' => 'CSK', 'national' => '0', 'sport' => 'Football'),
                'Bate Borisov'              => array('iso' => 'BBS', 'national' => '0', 'sport' => 'Football'),
                'Zenith Saint Petesrbourgh' => array('iso' => 'ZSP', 'national' => '0', 'sport' => 'Football'),
                'Apoel Nicosie'             => array('iso' => 'APN', 'national' => '0', 'sport' => 'Football'),
                'FC Bale'                   => array('iso' => 'BAL', 'national' => '0', 'sport' => 'Football'),
                'Sporting Portugal'         => array('iso' => 'SPO', 'national' => '0', 'sport' => 'Football'),
                'Bayer Leverkusen'          => array('iso' => 'BYL', 'national' => '0', 'sport' => 'Football'),
                'Shaktar Donetsk'           => array('iso' => 'SKD', 'national' => '0', 'sport' => 'Football'),
                'Manchester City'           => array('iso' => 'MCY', 'national' => '0', 'sport' => 'Football'),
                'Juventus'                  => array('iso' => 'JUV', 'national' => '0', 'sport' => 'Football'),
                'Malmo FF'                  => array('iso' => 'MAL', 'national' => '0', 'sport' => 'Football'),
                'Athletico Madrid'          => array('iso' => 'ATM', 'national' => '0', 'sport' => 'Football'),
                'Olympiakos Le Pirée'       => array('iso' => 'OLY', 'national' => '0', 'sport' => 'Football'),
                

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