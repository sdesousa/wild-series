<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use App\Entity\Episode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use faker;
use Faker\Factory;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $faker  =  Factory::create('fr_FR');
        for ($i = 0; $i < 3000; $i++) {
            $comment = new Comment();
            $comment->setComment($faker->text(255));
            $comment->setRate($faker->numberBetween(0, 5));
            $comment->setEpisode($this->getReference('episode_' . rand(0, 599)));
            $comment->setAuthor($this->getReference('subscriberauthor'));
            $manager->persist($comment);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [EpisodeFixtures::class];
    }
}
