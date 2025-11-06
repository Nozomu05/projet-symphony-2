<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setName('product '. $i);
            $product->setUnitPrice(mt_rand(10, 100));
            $product->setCreatedAt(new \Datetime('now'));
            $product->setStorage(10);
            $category = $manager->getRepository(Category::class)->find(rand(1, 5));
            $product->setCategory($category);
            $manager->persist($product);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }
}
