<?php

namespace Ady\Bundle\MaintenanceBundle\Drivers\Query;

/**
 * Abstract class to handle PDO connection.
 *
 * @author  Gilles Gauthier <g.gauthier@lexik.fr>
 */
abstract class PdoQuery
{
    /**
     * @var \PDO
     */
    protected $db;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor PdoDriver.
     *
     * @param array $options Options driver
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Execute create query.
     *
     * @return void
     */
    abstract public function createTableQuery(): void;

    /**
     * Result of delete query.
     *
     * @param \PDO $db PDO instance
     *
     * @return bool
     */
    abstract public function deleteQuery(\PDO $db): bool;

    /**
     * Result of select query.
     *
     * @param \PDO $db PDO instance
     *
     * @return array
     */
    abstract public function selectQuery(\PDO $db): array;

    /**
     * Result of insert query.
     *
     * @param ?int  $ttl ttl value
     * @param \PDO $db  PDO instance
     *
     * @return bool
     */
    abstract public function insertQuery(?int $ttl, \PDO $db): bool;

    /**
     * Initialize pdo connection.
     *
     * @return \PDO
     */
    abstract public function initDb(): \PDO;

    /**
     * Execute sql.
     *
     * @param \PDO   $db    PDO instance
     * @param string $query Query
     * @param array  $args  Arguments
     *
     * @return bool
     *
     * @throws \RuntimeException
     */
    protected function exec(\PDO $db, string $query, array $args = []): bool
    {
        $stmt = $this->prepareStatement($db, $query);

        $this->bindValues($stmt, $args);

        $success = $stmt->execute();

        if (!$success) {
            throw new \RuntimeException(sprintf('Error executing query "%s"', $query));
        }

        return $success;
    }

    /**
     * PrepareStatement.
     *
     * @param \PDO   $db    PDO instance
     * @param string $query Query
     *
     * @return \PDOStatement
     *
     * @throws \RuntimeException
     */
    protected function prepareStatement(\PDO $db, string $query): \PDOStatement
    {
        try {
            $stmt = $db->prepare($query);
        } catch (\Exception $e) {
            $stmt = false;
        }

        if (false === $stmt) {
            throw new \RuntimeException('The database cannot successfully prepare the statement');
        }

        return $stmt;
    }

    /**
     * Fetch All.
     *
     * @param \PDO   $db    PDO instance
     * @param string $query Query
     * @param array  $args  Arguments
     *
     * @return array
     */
    protected function fetch(\PDO $db, string $query, array $args = [])
    {
        $stmt = $this->prepareStatement($db, $query);

        $this->bindValues($stmt, $args);

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param \PDOStatement $stmt
     * @param array         $args
     * @return void
     */
    private function bindValues(\PDOStatement $stmt, array $args)
    {
        foreach ($args as $arg => $val) {
            $stmt->bindValue($arg, $val, is_int($val) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
    }
}
