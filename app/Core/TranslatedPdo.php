<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

/**
 * PDO wrapper - prepare(), query(), exec() cagrilarinda SQL'i otomatik
 * SqlTranslator'dan gecirir. Driver-agnostic repository kodu icin kullanilir.
 *
 * Kullanim: Repository'lerdeki $this->db = Database::connection() yerine
 * Database::connection() artik TranslatedPdo dondurur, ayni API ile calisir.
 */
class TranslatedPdo
{
    private PDO $pdo;
    private SqlTranslator $translator;

    public function __construct(PDO $pdo, SqlTranslator $translator)
    {
        $this->pdo = $pdo;
        $this->translator = $translator;
    }

    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return $this->pdo->prepare($this->translator->translate($query), $options);
    }

    public function query(string $query): PDOStatement|false
    {
        return $this->pdo->query($this->translator->translate($query));
    }

    public function exec(string $statement): int|false
    {
        return $this->pdo->exec($this->translator->translate($statement));
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    public function lastInsertId(?string $name = null): string|false
    {
        return $this->pdo->lastInsertId($name);
    }

    public function errorCode(): ?string
    {
        return $this->pdo->errorCode();
    }

    public function errorInfo(): array
    {
        return $this->pdo->errorInfo();
    }

    public function getAttribute(int $attribute): mixed
    {
        return $this->pdo->getAttribute($attribute);
    }

    public function setAttribute(int $attribute, mixed $value): bool
    {
        return $this->pdo->setAttribute($attribute, $value);
    }

    public function quote(string $string, int $type = PDO::PARAM_STR): string|false
    {
        return $this->pdo->quote($string, $type);
    }

    /**
     * Gercek PDO nesnesine erisim (gerekirse).
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}
