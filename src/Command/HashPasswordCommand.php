<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

#[AsCommand(
    name: 'app:hash-password',
    description: 'Генерация bcrypt хеша пароля',
)]
class HashPasswordCommand extends Command
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this->addArgument('password', InputArgument::REQUIRED, 'Пароль для хеширования');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $plainPassword = $input->getArgument('password');

        $user = new User();
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);

        $io->success('Хеш пароля:');
        $io->text($hashedPassword);

        return Command::SUCCESS;
    }
}
