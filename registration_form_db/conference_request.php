<?php

class ConferenceRequest {
    private ?int $id;
    private string $firstName;
    private string $lastName;
    private string $email;
    private string $phone;
    private ?int $subjectId;
    private ?int $paymentId;
    private bool $mailing;
    private string $ip;
    private ?string $createdAt;
    private ?string $deletedAt;

    private string $topic = '';
    private string $payment = '';

    public function __construct(array $data = []) {
        $this->id        = $data['id'] ?? null;
        $this->firstName = $data['first_name'] ?? '';
        $this->lastName  = $data['last_name'] ?? '';
        $this->email     = $data['email'] ?? '';
        $this->phone     = $data['phone'] ?? '';
        $this->mailing   = in_array($data['newsletter'] ?? '', ['Да', '1', 1, true], true);
        $this->ip        = $data['ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $this->createdAt = $data['created_at'] ?? $data['datetime'] ?? null;
        $this->deletedAt = $data['deleted_at'] ?? null;

        if (isset($data['subject_id'])) {
            $this->subjectId = (int) $data['subject_id'];
        } elseif (isset($data['topic']) && is_numeric($data['topic'])) {
            $this->subjectId = (int) $data['topic'];
        } else {
            $this->subjectId = null;
        }

        if (isset($data['payment_id'])) {
            $this->paymentId = (int) $data['payment_id'];
        } elseif (isset($data['payment']) && is_numeric($data['payment'])) {
            $this->paymentId = (int) $data['payment'];
        } else {
            $this->paymentId = null;
        }

        if (isset($data['topic_name'])) {
            $this->topic = $data['topic_name'];
        }
        if (isset($data['payment_name'])) {
            $this->payment = $data['payment_name'];
        }
    }

    public function getId(): ?int           { return $this->id; }
    public function getFirstName(): string  { return $this->firstName; }
    public function getLastName(): string   { return $this->lastName; }
    public function getEmail(): string      { return $this->email; }
    public function getPhone(): string      { return $this->phone; }
    public function getSubjectId(): ?int    { return $this->subjectId; }
    public function getPaymentId(): ?int    { return $this->paymentId; }
    public function isMailing(): bool       { return $this->mailing; }
    public function getNewsletter(): string { return $this->mailing ? 'Да' : 'Нет'; }
    public function getIp(): string         { return $this->ip; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getDatetime(): ?string  { return $this->createdAt; }
    public function getTopic(): string      { return $this->topic; }
    public function getPayment(): string    { return $this->payment; }

    public function validate(): array {
        $errors = [];

        if (empty($this->firstName)) $errors[] = "Введите имя";
        if (empty($this->lastName))  $errors[] = "Введите фамилию";
        if (empty($this->email))     $errors[] = "Введите email";
        if (empty($this->phone))     $errors[] = "Введите телефон";
        if ($this->subjectId === null || $this->subjectId <= 0) $errors[] = "Выберите тематику";
        if ($this->paymentId === null || $this->paymentId <= 0) $errors[] = "Выберите метод оплаты";

        return $errors;
    }

    public function save(PDO $dbo): bool {
        $stmt = $dbo->prepare('
            INSERT INTO participants
                (name, lastname, email, tel, subject_id, payment_id, mailing, ip, created_at, updated_at)
            VALUES
                (:name, :lastname, :email, :tel, :subject_id, :payment_id, :mailing, :ip, NOW(), NOW())
        ');
        return $stmt->execute([
            ':name'       => $this->firstName,
            ':lastname'   => $this->lastName,
            ':email'      => $this->email,
            ':tel'        => $this->phone,
            ':subject_id' => $this->subjectId,
            ':payment_id' => $this->paymentId,
            ':mailing'    => $this->mailing ? 1 : 0,
            ':ip'         => $this->ip,
        ]);
    }


    public static function loadActive(PDO $dbo): array {
        $sql = "
            SELECT
                p.id,
                p.name     AS first_name,
                p.lastname AS last_name,
                p.email,
                p.tel      AS phone,
                p.subject_id,
                p.payment_id,
                p.mailing,
                p.ip,
                p.created_at,
                p.deleted_at,
                s.name  AS topic_name,
                pay.name AS payment_name
            FROM participants p
            LEFT JOIN subjects  s   ON p.subject_id = s.id
            LEFT JOIN payments  pay ON p.payment_id = pay.id
            WHERE p.deleted_at IS NULL
            ORDER BY p.created_at DESC
        ";
        $rows = $dbo->query($sql)->fetchAll();

        $requests = [];
        foreach ($rows as $row) {
            $requests[] = new self($row);
        }
        return $requests;
    }

    public static function markDeleted(PDO $dbo, array $ids): bool {
        if (empty($ids)) return false;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $dbo->prepare("UPDATE participants SET deleted_at = NOW() WHERE id IN ($placeholders) AND deleted_at IS NULL");
        return $stmt->execute($ids);
    }
}