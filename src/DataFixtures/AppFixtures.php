<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Figures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    private $faker;
    private UserPasswordHasherInterface $hasher;
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->faker = Factory::create();
        $this->hasher = $hasher;
    }
    public function load(ObjectManager $manager): void
    {
        /*for ($i = 0; $i < 10 ; $i++)
        {
            $user = new User();
            $user -> setPseudo($this->faker->name())
                ->setEmail($this->faker->email())
                ->setRoles(['ROLE USER']);

            $hashPassword = $this->hasher->hashPassword(
                $user,
                'password'
            );
            $user->setPassword($hashPassword);
            $manager->persist($user);
        }*/

        for ($i = 0; $i < 10 ; $i++)
        {
            $f = new Figures();
            $f -> setCreatedAt($this->faker->dateTimeThisYear())
                ->setIllustrations(['P6/uploads/figure/maxence-64eb55646b86b.png'])
                ->setmovie('https://www.youtube.com/watch?v=d5cFeUmYu98')
            ->setName($this->faker->name())
            ->setDescription('description de la figure test')
            ->setSlug($this->faker->name());
            
            $manager->persist($f);
        }
        $manager->flush();
    }
}
