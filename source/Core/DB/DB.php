<?php

namespace CD\Core\DB;

use CD\Config\Config;
use Exception;
use PDO;
use PDOException;


class DB
{
    static private ?PDO $connection = null;

    /**
     * @throws Exception
     */
    public function db(): PDO
    {
        if (is_null(self::$connection)) {
            try {

                $options = array(
                    PDO::ATTR_PERSISTENT => true,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                );
                self::$connection = new PDO(Config::DB_DSN(), Config::DB_USER(), Config::DB_PASS(), $options);
            } catch (PDOException $e) {
                throw new Exception('DB ERROR: ' . $e->getMessage() . PHP_EOL);
            }
        }
        return self::$connection;
    }

    public function applyMigrations()
    {
        $this->createMigrationsTable();
        $applied_migrations = $this->getAppliedMigrations();
        $new_migrations = [];
        $files = scandir(MIGRATIONS_PATH);
        $migration_files = array_diff($files, $applied_migrations, ['.', '..']);  // extract the unapplied migration. this would result ([2] => m000x_xxxxx.php)

        // $migration_file = $migration_file[2];
        // require_once MIGRATIONS_PATH . '/' . $migration_file;
        // $class = pathinfo($migration_file, PATHINFO_FILENAME);

        foreach ($migration_files as $migration_file) {
            if ($migration_file === '..' || $migration_file === '.') {
                continue;
            }

            require_once MIGRATIONS_PATH . '/' . $migration_file;
            $class = pathinfo($migration_file, PATHINFO_FILENAME);
            $instance = new $class();
            echo $this->log("Applying migration $migration_file" . PHP_EOL);
            $instance->up();
            echo $this->log("Applied migration $migration_file" . PHP_EOL);
            $new_migrations[] = $migration_file;
        }
        if (!empty($new_migrations)) {
            $this->saveMigrations($new_migrations);
        } else {
            echo $this->log('All migrations are applied.' . PHP_EOL);
        }
    }

    public function getAppliedMigrations(): array
    {
        $statement = $this->db()->prepare('SELECT MigrationName FROM Migrations');
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    protected function createMigrationsTable(): void
    {
        $this->db()->exec("
        CREATE TABLE IF NOT EXISTS Migrations (
            MigrationID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
            MigrationName VARCHAR(255),
            MigrationCreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
        ) ENGINE=INNODB;
        ");
    }

    protected function saveMigrations(array $migrations): void
    {
        $values = join(', ', array_map(fn($m) => "('$m')", $migrations));
        $sql = "INSERT INTO Migrations (MigrationName) VALUES $values";
        $statement = $this->db()->prepare($sql);
        $statement->execute();
    }

    protected function log(string $message): string
    {
        // TODO: create log file
        return '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL;
    }
}