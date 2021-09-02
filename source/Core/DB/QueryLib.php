<?php


namespace CD\Core\DB;

use Exception;
use PDO;
use PDOException;

abstract class QueryLib extends DB
{
    protected string $sql = '';
    protected int $limit;
    protected array $bind = [];
    protected $result_list = null;
    protected array $fetch_mode = [];

    protected const DEFAULT_FETCH_MODE = PDO::FETCH_ASSOC;

    public const LEFT_TABLE_IDENTIFIER = 'left_col_';
    public const RIGHT_TABLE_IDENTIFIER = 'right_col_';

    abstract public function tableName(): string;

    abstract public function columns(): array;

    abstract public function primaryKey(): string;

    public function __call(string $name, array $arguments)
    {
        return $this->result_list;
    }

    /**
     * @throws Exception
     */
    public function select($columns = ['*'], $prefix_idetifier = self::LEFT_TABLE_IDENTIFIER): self
    {
        $col_keys = $this->joinColumns($columns, $prefix_idetifier);
        $sql = "SELECT $col_keys FROM " . $this->tableName() . ' AS ' . $prefix_idetifier;
        $this->sql = $sql;
        return $this;
    }

    public function raw(string $sql): self
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function where(array $clause): self
    {
        if (empty($this->sql)) {
            throw new Exception('SQL is not set');
        }
        $where = $this->joinClause($clause, 'AND');
        $this->sql .= " WHERE $where";
        $this->bind = $clause;
        return $this;
    }

    /**
     * @throws Exception
     */
    // public function LeftJoin(string $table_name, array $cols, array $on, $prefix_identifier = self::RIGHT_TABLE_IDENTIFIER): self
    // {
    //     $this->query['join'] = "LEFT JOIN";
    //     $this->query['right_table'] = "$table_name";
    //     $columns = $this->query['columns'] ? $this->query['columns'] . ', ' : '';
    //     $this->query['columns'] = $columns . $this->joinColumns($cols, $prefix_identifier);
    //
    //     return $this;
    // }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function fetch(array $options = [])
    {
        if (empty($this->sql)) {
            throw new Exception('Query is empty');
        }
        $sql = $this->sql;
        if (!empty($this->limit)) {
            $sql .= ' ' . $this->limit;
        }
        try {
            $stmt = $this->db()->prepare($sql);
            if (!empty($this->bind)) {
                foreach ($this->bind as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
            }
            $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
        if (has_key_presence('mode', $options)) {
            $stmt->setFetchMode(...$options['mode']);
            unset($options['mode']);
        } else {
            $stmt->setFetchMode(self::DEFAULT_FETCH_MODE);
        }
        return $stmt->fetch(...$options);
    }

    public function setFetchMode(array $options): self
    {
        $this->fetch_mode = $options;
        return $this;
    }

    /**
     * @throws Exception
     */
    // public function findBySQL(string $sql): bool
    // {
    //     try {
    //         $result = $this->db()->prepare($sql)->execute();
    //     } catch (PDOException $e) {
    //         throw new Exception($e->getMessage());
    //     }
    //     return $result;
    // }

    // public function save()
    // {
    //     $table = $this->tableName();
    //     $attrs = $this->columns();
    //     $columns = join(', ', $attrs);
    //     $params = ':' . str_replace(', ', ', :', $columns);
    //     // $params = array_map(fn($p) => ":$p", $attrs);
    //     $statement = $this->prepare("INSERT INTO $table ($columns) VALUES ($params)");
    // }

    // public function prepare(string $sql): PDOStatement
    // {
    //     return $this->db()->prepare($sql);
    // }

    protected function joinClause(array $clause, string $separator = ', ', string $prefix_identifier = self::LEFT_TABLE_IDENTIFIER): string
    {
        $clause = array_map(fn($val) => $prefix_identifier . ".$val = :$val", array_keys($clause));
        $separator = trim($separator);
        return join( " $separator ", $clause);
    }

    protected function joinColumns(array $columns, string $prefix_identifier): string
    {
        return $table = $this->tableName();
        $s = $this->db->prepare("SELECT * FROM $table");
        $s->execute();
        return $s->fetchAll();
    }
}