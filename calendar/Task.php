<?php

class Task {
    private ?int $id;
    private string $title;
    private ?int $typeId;
    private string $location;
    private string $taskDatetime;
    private int $duration;
    private string $comment;
    private bool $isCompleted;
    
    private string $typeName = '';

    public function __construct(array $data = []) {
        $this->id           = isset($data['id']) ? (int)$data['id'] : null;
        $this->title        = $data['title'] ?? '';
        $this->typeId       = isset($data['type_id']) ? (int)$data['type_id'] : null;
        $this->location     = $data['location'] ?? '';
        
        if (isset($data['task_date']) && isset($data['task_time'])) {
            $this->taskDatetime = $data['task_date'] . ' ' . $data['task_time'] . ':00';
        } else {
            $this->taskDatetime = $data['task_datetime'] ?? '';
        }

        $this->duration     = isset($data['duration']) ? (int)$data['duration'] : 60;
        $this->comment      = $data['comment'] ?? '';
        $this->isCompleted  = isset($data['is_completed']) ? (bool)$data['is_completed'] : false;

        if (isset($data['type_name'])) {
            $this->typeName = $data['type_name'];
        }
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getTypeId(): ?int { return $this->typeId; }
    public function getLocation(): string { return $this->location; }
    public function getTaskDatetime(): string { return $this->taskDatetime; }
    public function getDuration(): int { return $this->duration; }
    public function getComment(): string { return $this->comment; }
    public function isCompleted(): bool { return $this->isCompleted; }
    public function getTypeName(): string { return $this->typeName; }

    public function validate(): array {
        $errors = [];
        if (empty($this->title)) $errors[] = "Введите тему задачи";
        if (empty($this->typeId)) $errors[] = "Выберите тип задачи";
        if (empty($this->taskDatetime) || strlen($this->taskDatetime) < 19) $errors[] = "Укажите корректную дату и время";
        if ($this->duration <= 0) $errors[] = "Выберите длительность";
        return $errors;
    }

    public function save(PDO $dbo): bool {
        if ($this->id) {
            $stmt = $dbo->prepare('
                UPDATE tasks SET 
                    title = :title, type_id = :type_id, location = :location, 
                    task_datetime = :task_datetime, duration = :duration, 
                    comment = :comment, is_completed = :is_completed
                WHERE id = :id
            ');
            return $stmt->execute([
                ':id' => $this->id,
                ':title' => $this->title,
                ':type_id' => $this->typeId,
                ':location' => $this->location,
                ':task_datetime' => $this->taskDatetime,
                ':duration' => $this->duration,
                ':comment' => $this->comment,
                ':is_completed' => $this->isCompleted ? 1 : 0
            ]);
        } else {
            $stmt = $dbo->prepare('
                INSERT INTO tasks (title, type_id, location, task_datetime, duration, comment, is_completed)
                VALUES (:title, :type_id, :location, :task_datetime, :duration, :comment, 0)
            ');
            return $stmt->execute([
                ':title' => $this->title,
                ':type_id' => $this->typeId,
                ':location' => $this->location,
                ':task_datetime' => $this->taskDatetime,
                ':duration' => $this->duration,
                ':comment' => $this->comment
            ]);
        }
    }

    public static function getById(PDO $dbo, int $id): ?self {
        $stmt = $dbo->prepare('SELECT * FROM tasks WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }

    public static function loadList(PDO $dbo, string $filter, ?string $dateFilter): array {
        $sql = "
            SELECT t.*, type.name AS type_name 
            FROM tasks t
            LEFT JOIN task_types type ON t.type_id = type.id
            WHERE 1=1
        ";
        $params = [];

        if ($filter === 'current') {
            $sql .= " AND t.is_completed = 0 AND t.task_datetime >= CURDATE()";
        } elseif ($filter === 'overdue') {
            $sql .= " AND t.is_completed = 0 AND t.task_datetime < CURDATE()";
        } elseif ($filter === 'completed') {
            $sql .= " AND t.is_completed = 1";
        }

        if ($dateFilter) {
            if ($dateFilter === 'today') {
                $sql .= " AND DATE(t.task_datetime) = CURDATE()";
            } elseif ($dateFilter === 'tomorrow') {
                $sql .= " AND DATE(t.task_datetime) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
            } elseif ($dateFilter === 'this_week') {
                $sql .= " AND YEARWEEK(t.task_datetime, 1) = YEARWEEK(CURDATE(), 1)";
            } elseif ($dateFilter === 'next_week') {
                $sql .= " AND YEARWEEK(t.task_datetime, 1) = YEARWEEK(DATE_ADD(CURDATE(), INTERVAL 1 WEEK), 1)";
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFilter)) {
                $sql .= " AND DATE(t.task_datetime) = :exact_date";
                $params[':exact_date'] = $dateFilter;
            }
        }

        $sql .= " ORDER BY t.task_datetime ASC";

        $stmt = $dbo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $tasks = [];
        foreach ($rows as $row) {
            $tasks[] = new self($row);
        }
        return $tasks;
    }
}
