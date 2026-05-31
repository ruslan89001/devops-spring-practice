<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251228999999 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление тестовых данных';
    }

    public function up(Schema $schema): void
    {
        $passwordHash = '$2y$12$uSfKM2xpXSU46M3KTucggeN8h3JCMEr1lSbwb3z6Zy7C4EaxlOu9q';

        $this->addSql("INSERT INTO users (id, name, email, password, roles, created_at, updated_at) VALUES 
            (1, 'Администратор', 'admin@test.ru', '$passwordHash', '[\"ROLE_ADMIN\", \"ROLE_USER\"]', NOW(), NOW()),
            (2, 'Иван Иванов', 'user@test.ru', '$passwordHash', '[\"ROLE_USER\"]', NOW(), NOW()),
            (3, 'Мария Петрова', 'maria@test.ru', '$passwordHash', '[\"ROLE_USER\"]', NOW(), NOW())
        ");

        $this->addSql("SELECT setval('users_id_seq', 3, true)");

        $this->addSql("INSERT INTO spaces (id, name, address, description, price, photo, status, created_at, updated_at) VALUES 
            (1, 'Коворкинг Центр', 'г. Москва, ул. Ленина, д. 10', 'Современное пространство с быстрым интернетом и удобными рабочими местами. Кофе и чай бесплатно.', 1500.00, NULL, 'active', NOW(), NOW()),
            (2, 'Creative Hub', 'г. Санкт-Петербург, Невский пр., 50', 'Креативное пространство для стартапов и фрилансеров. Переговорные комнаты, кухня, зона отдыха.', 2000.00, NULL, 'active', NOW(), NOW()),
            (3, 'Tech Space', 'г. Москва, ул. Арбат, 25', 'Технологичный коворкинг с мощным оборудованием. Идеально для программистов и дизайнеров.', 1800.00, NULL, 'active', NOW(), NOW()),
            (4, 'Business Lounge', 'г. Екатеринбург, ул. Малышева, 12', 'Премиум-коворкинг для бизнес-встреч. Конференц-залы, секретарь, парковка.', 2500.00, NULL, 'active', NOW(), NOW()),
            (5, 'Freelance Point', 'г. Новосибирск, пр. Красный, 8', 'Уютное пространство для фрилансеров. Тихая атмосфера, комфортные кресла.', 1200.00, NULL, 'active', NOW(), NOW()),
            (6, 'Startup Office', 'г. Москва, ул. Тверская, 15', 'Офис для молодых стартапов. Менторская поддержка, нетворкинг-мероприятия.', 1700.00, NULL, 'inactive', NOW(), NOW())
        ");

        $this->addSql("SELECT setval('spaces_id_seq', 6, true)");

        $this->addSql("INSERT INTO bookings (id, user_id, space_id, booking_date, status, created_at, updated_at) VALUES 
            (1, 2, 1, CURRENT_DATE + INTERVAL '2 days', 'active', NOW(), NOW()),
            (2, 2, 3, CURRENT_DATE + INTERVAL '5 days', 'active', NOW(), NOW()),
            (3, 3, 2, CURRENT_DATE + INTERVAL '1 day', 'active', NOW(), NOW()),
            (4, 3, 4, CURRENT_DATE + INTERVAL '7 days', 'active', NOW(), NOW()),
            (5, 2, 1, CURRENT_DATE - INTERVAL '3 days', 'cancelled', NOW(), NOW())
        ");

        $this->addSql("SELECT setval('bookings_id_seq', 5, true)");

        $this->addSql("INSERT INTO reviews (id, user_id, space_id, rating, comment, created_at, updated_at) VALUES 
            (1, 2, 1, 5, 'Отличное место! Быстрый интернет, удобные столы, приятная атмосфера.', NOW(), NOW()),
            (2, 3, 2, 4, 'Хороший коворкинг, но иногда шумновато. В целом доволен.', NOW(), NOW()),
            (3, 2, 3, 5, 'Идеально для программистов! Мощное оборудование, тихо, кофе бесплатный.', NOW(), NOW()),
            (4, 3, 4, 3, 'Дорого, но качественно. Подходит для важных встреч.', NOW(), NOW())
        ");

        $this->addSql("SELECT setval('reviews_id_seq', 4, true)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM reviews');
        $this->addSql('DELETE FROM bookings');
        $this->addSql('DELETE FROM spaces');
        $this->addSql('DELETE FROM users');
    }
}
