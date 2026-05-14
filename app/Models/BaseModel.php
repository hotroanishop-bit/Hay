<?php
/**
 * Base Model
 * Provides PDO connection and basic CRUD operations
 */

class BaseModel
{
    protected static ?PDO $pdo = null;
    protected string $table = '';
    protected array $fillable = [];
    protected string $primaryKey = 'id';

    /**
     * Get PDO connection instance
     */
    protected static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $config = require CONFIG_PATH . '/database.php';
            
            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );
            
            self::$pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        }
        
        return self::$pdo;
    }

    /**
     * Get the PDO instance (public accessor)
     */
    public function db(): PDO
    {
        return self::getConnection();
    }

    /**
     * Find a record by primary key
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }

    /**
     * Find all records with optional conditions
     */
    public function findAll(array $conditions = [], string $orderBy = '', int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $whereClauses[] = "{$column} = :{$column}";
                $params[$column] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
            if ($offset > 0) {
                $sql .= " OFFSET {$offset}";
            }
        }
        
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Find a single record by conditions
     */
    public function findBy(array $conditions): ?array
    {
        $results = $this->findAll($conditions, '', 1);
        return $results[0] ?? null;
    }

    /**
     * Create a new record
     */
    public function create(array $data): int
    {
        $filteredData = $this->filterFillable($data);
        
        $columns = implode(', ', array_keys($filteredData));
        $placeholders = ':' . implode(', :', array_keys($filteredData));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($filteredData);
        
        return (int) $this->db()->lastInsertId();
    }

    /**
     * Update a record by primary key
     */
    public function update(int $id, array $data): bool
    {
        $filteredData = $this->filterFillable($data);
        
        $setClauses = [];
        foreach (array_keys($filteredData) as $column) {
            $setClauses[] = "{$column} = :{$column}";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE {$this->primaryKey} = :id";
        $filteredData['id'] = $id;
        
        $stmt = $this->db()->prepare($sql);
        return $stmt->execute($filteredData);
    }

    /**
     * Delete a record by primary key
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Count records with optional conditions
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $whereClauses[] = "{$column} = :{$column}";
                $params[$column] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }
        
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return (int) $result['count'];
    }

    /**
     * Execute raw SQL query
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute raw SQL statement (for INSERT, UPDATE, DELETE)
     */
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Filter data to only include fillable fields
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Begin a database transaction
     */
    public function beginTransaction(): bool
    {
        return $this->db()->beginTransaction();
    }

    /**
     * Commit a database transaction
     */
    public function commit(): bool
    {
        return $this->db()->commit();
    }

    /**
     * Rollback a database transaction
     */
    public function rollback(): bool
    {
        return $this->db()->rollBack();
    }
}
