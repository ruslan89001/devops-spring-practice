<?php

namespace App\Service;

use App\Entity\Space;
use App\Repository\SpaceRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Psr\Log\LoggerInterface;

class SpaceService
{
    private SpaceRepository $spaceRepository;
    private LoggerInterface $logger;
    private string $uploadDir;

    public function __construct(
        SpaceRepository $spaceRepository,
        LoggerInterface $logger,
        string $projectDir
    ) {
        $this->spaceRepository = $spaceRepository;
        $this->logger = $logger;
        $this->uploadDir = $projectDir . '/public/uploads/spaces';

        // Создать папку если не существует
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function getActiveSpaces(): array
    {
        return $this->spaceRepository->findActiveSpaces();
    }

    public function getActiveSpacesLimit(int $limit = 6): array
    {
        return $this->spaceRepository->findActiveSpacesLimit($limit);
    }

    public function getAllSpaces(): array
    {
        return $this->spaceRepository->getAllSpaces();
    }

    public function getSpaceById(int $id): ?Space
    {
        return $this->spaceRepository->find($id);
    }

    public function createSpace(array $data, ?UploadedFile $photoFile = null): Space
    {
        $this->validateSpaceData($data);

        $space = new Space();
        $space->setName($data['name']);
        $space->setAddress($data['address']);
        $space->setDescription($data['description'] ?? null);
        $space->setPrice($data['price']);
        $space->setStatus($data['status'] ?? 'active');

        if ($photoFile) {
            $photoPath = $this->uploadPhoto($photoFile);
            $space->setPhoto($photoPath);
        }

        $this->spaceRepository->save($space);

        $this->logger->info('Space created', ['space_id' => $space->getId()]);

        return $space;
    }

    public function updateSpace(Space $space, array $data, ?UploadedFile $photoFile = null): void
    {
        $this->validateSpaceData($data);

        $space->setName($data['name']);
        $space->setAddress($data['address']);
        $space->setDescription($data['description'] ?? null);
        $space->setPrice($data['price']);
        $space->setStatus($data['status'] ?? 'active');

        if ($photoFile) {
            if ($space->getPhoto()) {
                $this->deletePhoto($space->getPhoto());
            }

            $photoPath = $this->uploadPhoto($photoFile);
            $space->setPhoto($photoPath);
        }

        $this->spaceRepository->save($space);

        $this->logger->info('Space updated', ['space_id' => $space->getId()]);
    }

    public function deleteSpace(int $id): void
    {
        $space = $this->spaceRepository->find($id);

        if (!$space) {
            throw new \RuntimeException('Пространство не найдено');
        }

        if ($space->getPhoto()) {
            $this->deletePhoto($space->getPhoto());
        }

        $this->spaceRepository->remove($space);

        $this->logger->info('Space deleted', ['space_id' => $id]);
    }

    private function validateSpaceData(array $data): void
    {
        if (empty($data['name']) || strlen($data['name']) < 2 || strlen($data['name']) > 200) {
            throw new \InvalidArgumentException('Название должно быть от 2 до 200 символов');
        }

        if (empty($data['address'])) {
            throw new \InvalidArgumentException('Адрес обязателен');
        }

        if (empty($data['price']) || (float)$data['price'] <= 0) {
            throw new \InvalidArgumentException('Цена должна быть положительным числом');
        }

        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive'])) {
            throw new \InvalidArgumentException('Статус должен быть active или inactive');
        }
    }

    private function uploadPhoto(UploadedFile $file): string
    {
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new \RuntimeException('Размер файла не должен превышать 5MB');
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $extension = strtolower($file->guessExtension());

        if (!in_array($extension, $allowedExtensions)) {
            throw new \RuntimeException('Допустимые форматы: jpg, jpeg, png, gif');
        }

        $imageInfo = @getimagesize($file->getPathname());
        if ($imageInfo === false) {
            throw new \RuntimeException('Файл не является изображением');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $file->move($this->uploadDir, $filename);

        return $filename;
    }

    private function deletePhoto(string $filename): void
    {
        $filepath = $this->uploadDir . '/' . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
}
