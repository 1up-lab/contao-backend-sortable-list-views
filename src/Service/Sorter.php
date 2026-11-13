<?php

declare(strict_types=1);

namespace Oneup\ContaoBackendSortableListViewsBundle\Service;

use Doctrine\DBAL\Connection;

class Sorter
{
    private const SORT_GAP = 256;

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findRecord(string $table, int $id): ?array
    {
        if (0 === $id) {
            return null;
        }

        $record = $this->connection->fetchAssociative(
            \sprintf('SELECT * FROM %s WHERE id = :id', $this->connection->quoteIdentifier($table)),
            ['id' => $id],
        );

        return false === $record ? null : $record;
    }

    public function getNewSorting(int $newIndex): int
    {
        return (max(0, $newIndex) + 1) * self::SORT_GAP;
    }

    public function sort(string $table, int $id, int $oldIndex, int $newIndex): void
    {
        /**
         * < 0 --> move downward
         * = 0 --> do nothing
         * > 0 --> move upward.
         */
        $direction = $newIndex - $oldIndex;

        if (0 === $direction || null === $this->findRecord($table, $id)) {
            return;
        }

        $ids = $this->connection->fetchFirstColumn(
            \sprintf('SELECT id FROM %s ORDER BY sorting ASC, id ASC', $this->connection->quoteIdentifier($table)),
        );

        $ids = array_values(array_diff($ids, [$id]));
        array_splice($ids, max(0, min($newIndex, \count($ids))), 0, [$id]);

        $this->writeSorting($table, $ids);
    }

    public function resort(string $table): void
    {
        $ids = $this->connection->fetchFirstColumn(
            \sprintf('SELECT id FROM %s ORDER BY sorting ASC, id ASC', $this->connection->quoteIdentifier($table)),
        );

        $this->writeSorting($table, $ids);
    }

    public function sortInitial(string $table): void
    {
        if (!$this->needsInitialSorting($table)) {
            return;
        }

        $this->resort($table);
    }

    /**
     * @param list<mixed> $ids
     */
    private function writeSorting(string $table, array $ids): void
    {
        foreach ($ids as $index => $id) {
            $this->connection->update($this->connection->quoteIdentifier($table), [
                'sorting' => ($index + 1) * self::SORT_GAP,
            ], [
                'id' => $id,
            ]);
        }
    }

    private function needsInitialSorting(string $table): bool
    {
        $result = $this->connection->fetchOne(
            \sprintf('SELECT COUNT(id) FROM %s WHERE sorting = 0', $this->connection->quoteIdentifier($table)),
        );

        return $result > 0;
    }
}
