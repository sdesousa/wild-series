<?php

namespace App\DataFixtures;

use App\Entity\Episode;
use App\Entity\Season;
use App\Service\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use faker;
use Faker\Factory;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $faker  =  Factory::create('fr_FR');
        for ($i = 0; $i < 600; $i++) {
            $episode = new Episode();
            $slugify = new Slugify();
            $episode->setTitle($faker->text(255));
            $episode->setNumber($faker->numberBetween(0, 20));
            $episode->setSynopsis($faker->text);
            $episode->setSeason($this->getReference('season_' . rand(0, 59)));
            $episode->setSlug($slugify->generate($episode->getTitle()));
            $manager->persist($episode);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [SeasonFixtures::class];
    }
}
