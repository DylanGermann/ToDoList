<?php

namespace App\Tests\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Model\FakeUser;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserRepositoryTest extends KernelTestCase
{

    public function testRemove()
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $userIdentifier = "itestremovefunction@todolist.fr";
        $expectedUser = (new User())->setEmail($userIdentifier)
            ->setUsername("Im")
            ->setPassword("motdepasse")
            ->setRoles(["ROLE_USER"]);
        $userRepository->add($expectedUser, true);
        $user = $userRepository->findOneBy(["email" => $userIdentifier]);

        $userRepository->remove($user, true);
        $nullAtUser = $userRepository->findOneBy(["email" => $userIdentifier]);


        $this->assertNull($nullAtUser);
        $this->assertInstanceOf(User::class, $user);
    }

    public function testUpgradePassword()
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $expectedPassword = "imTheNewPassword";
        $userIdentifier = "second@todolist.fr";
        $user = (new User())->setEmail($userIdentifier)
            ->setUsername("Im")
            ->setPassword("motdepasse")
            ->setRoles(["ROLE_USER"]);

        $userRepository->upgradePassword($user, $expectedPassword);
        $password = ($newUser = $userRepository->findOneBy(["email" => $userIdentifier]))->getPassword();
        $userRepository->remove($newUser, true);

        $this->assertEquals($expectedPassword, $password);
    }

    public function testGetAllAdminUser()
    {
        $userRepository = $this->entityManager->getRepository(User::class);

        $users = $userRepository->getAllAdminUser();

        $this->assertIsArray($users);
        foreach($users as $adminUser) {
            $this->assertInstanceOf(User::class, $adminUser);
            $this->assertContains("ROLE_ADMIN", $adminUser->getRoles());
        }
    }

    public function testGetNormalUser()
    {
        $userRepository = $this->entityManager->getRepository(User::class);

        $users = $userRepository->getAllNormalUser();

        $this->assertIsArray($users);
        foreach($users as $user) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertNotContains("ROLE_ADMIN", $user->getRoles());
            $this->assertContains("ROLE_USER", $user->getRoles());
        }
    }

    public function testUpgradePasswordThrowResponse()
    {
        $this->expectException(UnsupportedUserException::class);

        $userRepository = $this->entityManager->getRepository(User::class);
        $expectedPassword = "imTheNewPassword";
        $userIdentifier = "second@todolist.fr";
        $user = new FakeUser();

        $userRepository->upgradePassword($user, $expectedPassword);


    }

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }
}
