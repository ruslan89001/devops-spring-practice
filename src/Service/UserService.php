<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Psr\Log\LoggerInterface;

class UserService
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private LoggerInterface $logger;

    public function __construct(
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        LoggerInterface $logger
    ) {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->logger = $logger;
    }

    public function registerUser(string $name, string $email, string $plainPassword): User
    {
        if ($this->userRepository->findByEmail($email)) {
            $this->logger->warning('Registration failed: email already exists', ['email' => $email]);
            throw new \RuntimeException('Пользователь с таким email уже существует');
        }

        if (strlen($name) < 2 || strlen($name) > 100) {
            throw new \InvalidArgumentException('Имя должно быть от 2 до 100 символов');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Некорректный формат email');
        }

        if (strlen($plainPassword) < 6) {
            throw new \InvalidArgumentException('Пароль должен быть минимум 6 символов');
        }

        $user = new User();
        $user->setName($name);
        $user->setEmail($email);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $user->setRoles(['ROLE_USER']);

        $this->userRepository->save($user);

        $this->logger->info('User registered successfully', ['user_id' => $user->getId(), 'email' => $email]);

        return $user;
    }

    public function updateUser(User $user, array $data): void
    {
        if (isset($data['name'])) {
            if (strlen($data['name']) < 2 || strlen($data['name']) > 100) {
                throw new \InvalidArgumentException('Имя должно быть от 2 до 100 символов');
            }
            $user->setName($data['name']);
        }

        if (isset($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Некорректный формат email');
            }

            $existingUser = $this->userRepository->findByEmail($data['email']);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                throw new \RuntimeException('Email уже используется другим пользователем');
            }

            $user->setEmail($data['email']);
        }

        if (isset($data['password']) && !empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                throw new \InvalidArgumentException('Пароль должен быть минимум 6 символов');
            }

            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        $this->userRepository->save($user);

        $this->logger->info('User updated', ['user_id' => $user->getId()]);
    }

    public function deleteUser(int $userId, User $currentUser): void
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            throw new \RuntimeException('Пользователь не найден');
        }

        if ($user->getId() === $currentUser->getId()) {
            throw new \RuntimeException('Вы не можете удалить свой собственный аккаунт');
        }

        $this->userRepository->remove($user);

        $this->logger->warning('User deleted', [
            'deleted_user_id' => $userId,
            'deleted_by' => $currentUser->getId()
        ]);
    }

    public function getAllUsers(): array
    {
        return $this->userRepository->getAllUsers();
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function createUser(string $name, string $email, string $plainPassword, array $roles = ['ROLE_USER']): User
    {
        if ($this->userRepository->findByEmail($email)) {
            throw new \RuntimeException('Пользователь с таким email уже существует');
        }

        $user = new User();
        $user->setName($name);
        $user->setEmail($email);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $user->setRoles($roles);

        $this->userRepository->save($user);

        $this->logger->info('User created by admin', ['user_id' => $user->getId()]);

        return $user;
    }
}
