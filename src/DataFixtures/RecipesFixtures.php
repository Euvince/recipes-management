<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Ingredient;
use App\Entity\Quantity;
use App\Entity\Recipe;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use FakerRestaurant\Provider\fr_FR\Restaurant;
use Symfony\Component\String\Slugger\SluggerInterface;

use function Symfony\Component\Clock\now;

class RecipesFixtures extends Fixture implements DependentFixtureInterface
{
    function __construct(
        private readonly SluggerInterface $slugger
    )
    {
    }

    function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');
        $faker->addProvider(new Restaurant($faker));

        $ingredients = array_map(fn (string $name) => (new Ingredient())
            ->setName($name)
            ->setSlug(strtolower($this->slugger->slug($name)))
            ->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTime()))
            ->setUpdatedAt(\DateTimeImmutable::createFromMutable($faker->dateTime())), [
            "Farine", "Sucre", "Oeufs", "Beurre", "Lait", "Sel", "Chocolat noir",
            "Vanille", "Fruits secs(amandes, noix, ect...)", "Pépites de chocolat"
        ]);

        $units = ["g", "kg", "L", "ml", 'cl', 'dL', 'c. à soupe', 'c. à café', 'verre', 'pincée'];

        $categories = ['Plat chaud', 'Dessert', 'Goûter', 'Plat principal', 'Entrée'];

        foreach ($categories as $c) {
            $category = (new Category())
                ->setName($c)
                ->setSlug(strtolower($this->slugger->slug($c)))
                ->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTime()))
                ->setUpdatedAt(\DateTimeImmutable::createFromMutable($faker->dateTime()))
            ;
            $this->addReference($c, $category);
            $manager->persist($category);
        }

        for ((int)$i = 0; $i < 30; $i++) {
            $recipe = (new Recipe())
                ->setTitle($faker->foodName())
                ->setContent($faker->paragraphs(rand(3, 10), true))
                ->setDuration($faker->numberBetween(0, 60))
                ->setCreatedAt(\DateTimeImmutable::createFromMutable($faker->dateTime()))
                ->setUpdatedAt(\DateTimeImmutable::createFromMutable($faker->dateTime()))
                ->setCategory($this->getReference($faker->randomElement($categories)))
                ->setUser($this->getReference("USER{$faker->numberBetween(1, 10)}"))
            ;
            $recipe->setSlug(strtolower($this->slugger->slug($recipe->getTitle())));

            foreach ($faker->randomElements($ingredients, $faker->numberBetween(2, 5)) as $ingredient) {
                $recipe->addQuantity((new Quantity())
                    ->setQuantity($faker->numberBetween(1, 250))
                    ->setUnit($faker->randomElement($units))
                    ->setIngredient($ingredient)
                );
            }

            $manager->persist($recipe);
        }

        $manager->flush();
    }

    function getDependencies() : array
    {
        return [
            UsersFixtures::class
        ];
    }

}
