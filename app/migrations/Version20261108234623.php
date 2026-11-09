<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261108234623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create expense_transaction table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE expense_transaction (
                id INT AUTO_INCREMENT NOT NULL,
                amount NUMERIC(10, 2) NOT NULL,
                category_id INT NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX IDX_EXPENSE_CATEGORY (category_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        ');

        $this->addSql('
            ALTER TABLE expense_transaction
            ADD CONSTRAINT FK_EXPENSE_CATEGORY
            FOREIGN KEY (category_id)
            REFERENCES category (id)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            ALTER TABLE expense_transaction
            DROP FOREIGN KEY FK_EXPENSE_CATEGORY
        ');

        $this->addSql('DROP TABLE expense_transaction');
    }
}
