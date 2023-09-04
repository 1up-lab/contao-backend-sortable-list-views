<?php

declare(strict_types=1);

namespace Oneup\ContaoBackendSortableListViewsBundle\Tests\DependencyInjection;

use Oneup\ContaoBackendSortableListViewsBundle\Controller\Sort;
use Oneup\ContaoBackendSortableListViewsBundle\DependencyInjection\OneupContaoBackendSortableListViewsExtension;
use Oneup\ContaoBackendSortableListViewsBundle\EventListener\LoadDataContainerListener;
use Oneup\ContaoBackendSortableListViewsBundle\Service\Sorter;
use Oneup\ContaoBackendSortableListViewsBundle\Service\TableNameExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OneupContaoBackendSortableListViewsExtensionTest extends TestCase
{
    public function testLoadsServicesYaml(): void
    {
        $extension = new OneupContaoBackendSortableListViewsExtension();
        $containerBuilder = new ContainerBuilder();

        $extension->load([], $containerBuilder);
        $definitions = array_keys($containerBuilder->getDefinitions());

        self::assertContains(LoadDataContainerListener::class, $definitions);
        self::assertContains(Sorter::class, $definitions);
        self::assertContains(TableNameExtractor::class, $definitions);
        self::assertContains(Sort::class, $definitions);
        self::assertCount(5, $definitions);
    }
}
