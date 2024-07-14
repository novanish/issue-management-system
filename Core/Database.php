<?php

namespace Core;

use PDO, PDOException, PDOStatement;

/**
 * Class Database
 * A simple PDO database wrapper for handling database connections and queries.
 */
class Database
{
    /**
     * @var PDO The PDO instance representing the database connection.
     */
    private PDO $pdo;

    /**
     * Database constructor.
     *
     * @param string $driver The database driver (e.g., 'mysql', 'pgsql').
     * @param array $config An array containing database connection configuration parameters.
     * @param array $credentials An array containing database connection credentials.
     */
    public function __construct(string $driver, array $config, array $credentials, array $options = [])
    {
        // Construct DSN string from configuration parameters
        $dsn = $this->buildDsn($driver, $config);

        // Default PDO options
        $options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ] + $options;

        try {
            // Create PDO instance
            $this->pdo = new PDO($dsn, $credentials['username'], $credentials['password'], $options);
        } catch (PDOException $ex) {
            die("Database connection failed: " . $ex->getMessage());
        }
    }

    /**
     * Executes a query on the database.
     *
     * @param string $queryString The SQL query string.
     * @param array|null $params An array of parameters to bind to the query.
     * @return PDOStatement The PDOStatement object representing the result of the query.
     */
    public function query(string $queryString, ?array $params = [], ?array $types = null): PDOStatement
    {
        $statement = $this->pdo->prepare($queryString);

        if ($types !== null) {
            foreach ($params as $param => $value) {
                $type = isset($types[$param]) ? $types[$param] : null;
                $statement->bindValue($param, $value, $type);
            }
        }


        $statement->execute($types === null ? $params : null);

        return $statement;
    }


    /**
     * Builds the DSN (Data Source Name) string for PDO connection.
     *
     * @param string $driver The database driver (e.g., 'mysql', 'pgsql').
     * @param array $config An array containing database connection configuration parameters.
     * @return string The constructed DSN string.
     */
    private function buildDsn(string $driver, array $config): string
    {
        $dsnParams = http_build_query($config, arg_separator: ';');
        return "{$driver}:{$dsnParams}";
    }

    /**
     * Handles a database transaction.
     *
     * Begins a transaction, executes the provided transactional function, and commits the transaction.
     * If an exception occurs, rolls back the transaction and optionally handles the error using the provided error handler.
     *
     * @param callable $transactionalFunction The function to execute within the transaction. This function should accept the current instance as its parameter.
     * @param callable|null $errorHandler An optional function to handle any \PDOException that occurs. This function should accept the exception as its parameter.
     *
     * @return void
     *
     * @throws \PDOException If an error occurs during the transaction and no error handler is provided.
     */
    public function transaction(callable $transactionalFunction, ?callable $errorHandler = null)
    {
        try {
            $this->pdo->beginTransaction();
            $transactionalFunction($this);
            $this->pdo->commit();
        } catch (\PDOException $ex) {
            $this->pdo->rollBack();
            is_callable($errorHandler) ?  $errorHandler($ex) : throw $ex;
        }
    }
}
