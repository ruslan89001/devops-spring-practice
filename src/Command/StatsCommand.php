<?php

namespace App\Command;

use App\Service\AdminService;
use App\Repository\BookingRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:stats',
    description: 'Вывод статистики системы бронирования',
)]
class StatsCommand extends Command
{
    private AdminService $adminService;
    private BookingRepository $bookingRepository;

    public function __construct(AdminService $adminService, BookingRepository $bookingRepository)
    {
        parent::__construct();
        $this->adminService = $adminService;
        $this->bookingRepository = $bookingRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Статистика системы бронирования коворкингов');

        $stats = $this->adminService->getDashboardStats();

        $activeBookings = $this->bookingRepository->count(['status' => 'active']);
        $cancelledBookings = $this->bookingRepository->count(['status' => 'cancelled']);

        $io->section('Общая статистика');
        $io->table(
            ['Параметр', 'Значение'],
            [
                ['Всего пользователей', $stats['total_users']],
                ['Всего пространств', $stats['total_spaces']],
                ['Активных пространств', $stats['active_spaces']],
                ['Всего отзывов', $stats['total_reviews']],
                ['Активных бронирований', $activeBookings],
                ['Отменённых бронирований', $cancelledBookings],
            ]
        );

        $io->success('Статистика успешно выведена!');

        return Command::SUCCESS;
    }
}
