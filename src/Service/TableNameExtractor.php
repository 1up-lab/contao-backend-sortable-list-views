<?php

declare(strict_types=1);

namespace Oneup\ContaoBackendSortableListViewsBundle\Service;

class TableNameExtractor
{
    public function getTableNameFromAction(?string $action): ?string
    {
        if (null === $action || '' === $action) {
            return null;
        }

        foreach ($GLOBALS['BE_MOD'] as $category) {
            if (\array_key_exists($action, $category)) {
                return $category[$action]['tables'][0] ?? null;
            }
        }

        return null;
    }
}
