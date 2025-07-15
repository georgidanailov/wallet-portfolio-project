<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            // Gaming
            ['name' => 'Cyberpunk 2077', 'price' => 59.99, 'category' => 0],
            ['name' => 'The Witcher 3', 'price' => 39.99, 'category' => 0],
            ['name' => 'Elden Ring', 'price' => 49.99, 'category' => 0],
            ['name' => 'Steam Deck', 'price' => 399.99, 'category' => 0],

            // Electronics
            ['name' => 'Gaming Mouse', 'price' => 79.99, 'category' => 1],
            ['name' => 'Mechanical Keyboard', 'price' => 129.99, 'category' => 1],
            ['name' => 'Gaming Headset', 'price' => 99.99, 'category' => 1],
            ['name' => 'Webcam HD', 'price' => 89.99, 'category' => 1],

            // Books
            ['name' => 'Clean Code', 'price' => 29.99, 'category' => 2],
            ['name' => 'PHP: The Right Way', 'price' => 24.99, 'category' => 2],
            ['name' => 'Symfony Handbook', 'price' => 34.99, 'category' => 2],

            // Software
            ['name' => 'PhpStorm License', 'price' => 199.99, 'category' => 3],
            ['name' => 'Photoshop CC', 'price' => 20.99, 'category' => 3],
            ['name' => 'Office 365', 'price' => 69.99, 'category' => 3],

            // Music
            ['name' => 'Spotify Premium', 'price' => 9.99, 'category' => 4],
            ['name' => 'Music Album: Greatest Hits', 'price' => 12.99, 'category' => 4],

            // Movies
            ['name' => 'The Matrix 4K', 'price' => 19.99, 'category' => 5],
            ['name' => 'Avengers Collection', 'price' => 49.99, 'category' => 5],
        ];

        foreach ($products as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            $product->setPrice($productData['price']);

            $category = $this->getReference('category_' . $productData['category'], Category::class);
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