<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version202512289999991 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique constraint for active bookings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX unique_active_booking ON bookings (space_id, booking_date) WHERE status = \'active\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX unique_active_booking');
    }
}
