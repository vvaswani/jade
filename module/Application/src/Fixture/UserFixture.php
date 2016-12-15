<?php
namespace Application\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Application\Entity\User;

class UserFixtureLoader implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('admin@example.com');
        $user->setName('Administrator');
        $user->setStatus(User::STATUS_ACTIVE);
        $user->setCreated(new \DateTime("now"));
        $user->setPassword('$2y$10$fQ1SOo48Z193Wlnmyq52mec5H4iC/UXmk0DDLMNp.FgmBDplisSZK');

        $manager->persist($user);
        $manager->flush();
    }
}