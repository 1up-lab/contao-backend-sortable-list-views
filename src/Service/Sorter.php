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

    public function sort(string $table, int $id, int $oldIndex, int $newIndex): void
    {
        if (0 === $id) {
            return;
        }

        /**
         * < 0 --> move downward
         * = 0 --> do nothing
         * > 0 --> move upward.
         */
        $direction = $newIndex - $oldIndex;

        if (0 === $direction) {
            return;
        }

        /** @var array $entry */
        $entry = $this->connection
            ->prepare(\sprintf('SELECT id, sorting FROM %s WHERE id = :id', $table))
            ->executeQuery(['id' => $id])
            ->fetchAssociative()
        ;

        $sortingDiff = $direction + $direction * self::SORT_GAP;

        $this->connection->update($table, [
            'sorting' => $entry['sorting'] + $sortingDiff,
        ], [
            'id' => $id,
        ]);

        $this->resort($table);
    }

    public function resort(string $table): void
    {
        $entries = $this->connection
            ->prepare(\sprintf('SELECT id, sorting FROM %s ORDER BY sorting ASC', $table))
            ->executeQuery()
            ->fetchAllAssociative()
        ;

        foreach ($entries as $index => $entry) {
            $this->connection->update($table, [
                'sorting' => ($index + 1) * self::SORT_GAP,
            ], [
                'id' => $entry['id'],
            ]);
        }
    }

    public function sortInitial(string $table): void
    {
        if (!$this->needsInitialSorting($table)) {
            return;
        }

        $this->resort($table);
    }

    private function needsInitialSorting(string $table): bool
    {
        $result = $this->connection
            ->prepare(\sprintf('SELECT COUNT(id) FROM %s WHERE sorting = 0', $table))
            ->executeQuery()
            ->fetchOne()
        ;

        return $result > 0;
    }
}
