<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Gaming',
            'Electronics',
            'Books',
            'Software',
            'Music',
            'Movies'
        ];

        foreach ($categories as $index => $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);

            // Add reference so we can use in ProductFixtures
            $this->addReference('category_' . $index, $category);
        }

        $manager->flush();
    }
}