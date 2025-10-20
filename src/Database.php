<?php declare(strict_types=1);

/**
 * This file is part of ChatGPT Speaker, a PHP Experts, Inc., Project.
 *
 * Copyright © 2024 PHP Experts, Inc.
 * Author: Theodore R. Smith <theodore.smith@autonomo.codes>
 *   GPG Fingerprint: 6CAC F838 454C 8912 8AA2  26DB 89DC D8F1 3BB9 33B3
 *   https://www.phpexperts.pro/
 *   https://www.autonomo.codes/
 *   https://github.com/AutonomoDev/ai-speaker
 *
 * This file is licensed under the Creative Commons No-Derivations v4.0 License.
 * Most rights are reserved.
 */

namespace Autonomo\AiSpeaker;

use PDO;
use PDOException;

class Database
{
    /**
     * @var string Path to the SQLite database file
     */
    private string $databasePath;

    /**
     * @var PDO PDO instance for database operations
     */
    private PDO $pdo;

    /**
     * Constructor
     *
     * Initializes the Database object by determining the application root
     * and setting the database path, then connects to the database.
     *
     * @throws PDOException if the PDO connection fails
     */
    public function __construct()
    {
        // Determine the application root directory using getcwd()
        $appRoot = $this->getAppRoot();

        // Define the path to the SQLite database file within the application root
        $this->databasePath = "$appRoot/chatgpt-database.db";

        $this->init();

        // Initialize the PDO connection
        $this->connect();
    }

    /**
     * Connect to the SQLite database using PDO
     *
     * Sets the error mode to exceptions for better error handling.
     *
     * @throws PDOException if the connection fails
     */
    private function connect(): void
    {
        try {
            // Create (or open) the SQLite database using PDO
            $this->pdo = new PDO('sqlite:' . $this->databasePath);

            // Set error mode to exceptions to handle errors gracefully
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new PDOException("Connection failed: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Initialize the Database
     *
     * If the database file doesn't exist, it will create it and the required table.
     * If it does exist but the table doesn't, it will attempt to create the table.
     */
    public function init(): void
    {
        // Check if the database file exists
        if (file_exists($this->databasePath)) {
            return;
        } else {
            $this->createChatGptUsedTokensTable();
            trigger_error("Database and table 'chatgpt_used_tokens' created successfully in '{$this->getAppRoot()}'.", E_USER_NOTICE);
            return;
        }

        // If file exists, check if the table exists; if not, create it
        if (!$this->tableExists('chatgpt_used_tokens')) {
            $this->createChatGptUsedTokensTable();
            trigger_error("Table 'chatgpt_used_tokens' created successfully in existing database at '{$this->getAppRoot()}'.", E_USER_NOTICE);
        } else {
            trigger_error("Database already initialized at '{$this->getAppRoot()}'.", E_USER_NOTICE);
        }
    }

    /**
     * Checks if a given table exists in the current database.
     *
     * @param string $tableName The name of the table to check.
     * @return bool True if the table exists, false otherwise.
     */
    private function tableExists(string $tableName): bool
    {
        try {
            $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$tableName';");
            return ($stmt && $stmt->fetchColumn()) ? true : false;
        } catch (PDOException $e) {
            // If something goes wrong, assume table does not exist
            return false;
        }
    }

    /**
     * Create the chatgpt_used_tokens Table
     *
     * Executes the SQL statement to create the table if it doesn't exist.
     *
     * @throws PDOException if the table creation fails
     */
    private function createChatGptUsedTokensTable(): void
    {
        // Define the SQL statement to create the table
        $createTableSQL = "
            CREATE TABLE IF NOT EXISTS chatgpt_used_tokens (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                api_project_id TEXT NOT NULL,
                api_project TEXT NOT NULL,
                tokens_used INTEGER NOT NULL
            );
        ";

        // Execute the SQL statement
        $this->pdo->exec($createTableSQL);
    }

    /**
     * Get the Application Root Directory
     *
     * Returns the application root directory determined by getcwd().
     *
     * @return string Application root directory path
     */
    public function getAppRoot(): string
    {
        $appRoot = getcwd();
        return $appRoot !== false ? $appRoot : '';
    }

    /**
     * Insert a Record into chatgpt_used_tokens Table
     *
     * Inserts a new record into the chatgpt_used_tokens table.
     *
     * @param string $apiProjectId
     * @param string $apiProject
     * @param int    $tokensUsed
     * @return int ID of the inserted record
     *
     * @throws PDOException if the insertion fails
     */
    public function insertUsedTokens(string $apiProjectId, string $apiProject, int $tokensUsed): int
    {
        $insertSQL = "
            INSERT INTO chatgpt_used_tokens (api_project_id, api_project, tokens_used)
            VALUES (:api_project_id, :api_project, :tokens_used);
        ";

        $stmt = $this->pdo->prepare($insertSQL);

        $stmt->bindParam(':api_project_id', $apiProjectId, PDO::PARAM_STR);
        $stmt->bindParam(':api_project', $apiProject, PDO::PARAM_STR);
        $stmt->bindParam(':tokens_used', $tokensUsed, PDO::PARAM_INT);

        $stmt->execute();

        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Close the PDO Connection
     *
     * Sets the PDO instance to null, effectively closing the connection.
     */
    public function closeConnection(): void
    {
        $this->pdo = null;
    }
}
