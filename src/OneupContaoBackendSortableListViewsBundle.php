<?php

declare(strict_types=1);

namespace Oneup\ContaoBackendSortableListViewsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class OneupContaoBackendSortableListViewsBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
