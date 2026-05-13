<?php

class ConferenceRequest {
    private string $id;
    private string $datetime;
    private string $firstName;
    private string $lastName;
    private string $email;
    private string $phone;
    private string $topic;
    private string $payment;
    private string $newsletter;
    private string $ip;
    private string $status;

    private const DELIMITER = '||';
    private const FILE_PATH = __DIR__ . '/requests.txt';

    public function __construct(array $data = []) {
        $this->id         = $data['id'] ?? uniqid();
        $this->datetime   = $data['datetime'] ?? date('Y-m-d H:i:s');
        $this->firstName  = $data['first_name'] ?? '';
        $this->lastName   = $data['last_name'] ?? '';
        $this->email      = $data['email'] ?? '';
        $this->phone      = $data['phone'] ?? '';
        $this->topic      = $data['topic'] ?? '';
        $this->payment    = $data['payment'] ?? '';
        $this->newsletter = $data['newsletter'] ?? 'Нет';
        $this->ip         = $data['ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $this->status     = $data['status'] ?? 'active';
    }

    public function getId(): string { return $this->id; }
    public function getDatetime(): string { return $this->datetime; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): string { return $this->phone; }
    public function getTopic(): string { return $this->topic; }
    public function getPayment(): string { return $this->payment; }
    public function getNewsletter(): string { return $this->newsletter; }
    public function getIp(): string { return $this->ip; }
    public function getStatus(): string { return $this->status; }

    public function validate(): array {
        $errors = [];

        if (empty($this->firstName)) $errors[] = "Введите имя";
        if (empty($this->lastName))  $errors[] = "Введите фамилию";
        if (empty($this->email))     $errors[] = "Введите email";
        if (empty($this->phone))     $errors[] = "Введите телефон";
        if (empty($this->topic))     $errors[] = "Выберите тематику";
        if (empty($this->payment))   $errors[] = "Выберите метод оплаты";

        $fieldsToCheck = [$this->firstName, $this->lastName, $this->email, $this->phone];
        foreach ($fieldsToCheck as $field) {
            if (strpos($field, self::DELIMITER) !== false) {
                $errors[] = "Строка `{$field}` содержит недопустимые символы";
            }
        }

        return $errors;
    }

    public function save(): bool {
        $line = implode(self::DELIMITER, [
            $this->id,
            $this->datetime,
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->phone,
            $this->topic,
            $this->payment,
            $this->newsletter,
            $this->ip,
            $this->status,
        ]) . PHP_EOL;

        return file_put_contents(self::FILE_PATH, $line, FILE_APPEND | LOCK_EX) !== false;
    }

    public static function loadAll(): array {
        $requests = [];
        if (!file_exists(self::FILE_PATH)) {
            return $requests;
        }

        $lines = file(self::FILE_PATH, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $parts = explode(self::DELIMITER, $line);
            if (count($parts) >= 11) {
                $requests[] = new self([
                    'id'         => $parts[0],
                    'datetime'   => $parts[1],
                    'first_name' => $parts[2],
                    'last_name'  => $parts[3],
                    'email'      => $parts[4],
                    'phone'      => $parts[5],
                    'topic'      => $parts[6],
                    'payment'    => $parts[7],
                    'newsletter' => $parts[8],
                    'ip'         => $parts[9],
                    'status'     => $parts[10],
                ]);
            }
        }
        return $requests;
    }

    public static function loadActive(): array {
        $all = self::loadAll();
        return array_filter($all, fn($r) => $r->getStatus() === 'active');
    }

    public static function markDeleted(array $ids): bool {
        $all = self::loadAll();
        $updated = false;

        foreach ($all as $request) {
            if (in_array($request->getId(), $ids)) {
                $request->status = 'deleted';
                $updated = true;
            }
        }

        if (!$updated) return false;

        // Перезаписать файл целиком
        $lines = [];
        foreach ($all as $request) {
            $lines[] = implode(self::DELIMITER, [
                $request->id,
                $request->datetime,
                $request->firstName,
                $request->lastName,
                $request->email,
                $request->phone,
                $request->topic,
                $request->payment,
                $request->newsletter,
                $request->ip,
                $request->status,
            ]);
        }

        return file_put_contents(self::FILE_PATH, implode(PHP_EOL, $lines) . PHP_EOL) !== false;
    }
}