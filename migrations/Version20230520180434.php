<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230520180434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create guess user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `selsys`.`user` (`id`, `email`, `roles`, `password`, `api_token`) VALUES (5, 'guess', '[]', '$2y$13$\JFrAYeFRAuKjHcwflqiN8O75Dl7mqSw3LXSAAQAofVRfmLhfisSTu', NULL);");
        $this->addSql("INSERT INTO `selsys`.`user_type` (`id`, `name`) VALUES (1, 'FREE_RECRUITER')");
        $this->addSql("INSERT INTO `selsys`.`user_type` (`id`, `name`) VALUES (2, 'FREE_PROGRAMMER')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
