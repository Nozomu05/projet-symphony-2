<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    // ALTER TABLE `category` AUTO_INCREMENT=1;

    public function load(ObjectManager $manager): void
    {
        $categories = ['sport', 'bricolage', 'decoration', 'jardinerie', 'luminaire'];

        foreach($categories as $value) {
            $categoryObj = new Category();
            $categoryObj->setName($value);
            $manager->persist($categoryObj);
        }

        $manager->flush();
    }
}
