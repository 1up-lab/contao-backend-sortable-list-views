<?php

declare(strict_types=1);

namespace Oneup\ContaoBackendSortableListViewsBundle\Controller;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Oneup\ContaoBackendSortableListViewsBundle\Service\Sorter;
use Oneup\ContaoBackendSortableListViewsBundle\Service\TableNameExtractor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Sort
{
    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly TableNameExtractor $tableNameExtractor,
        private readonly Sorter $sorter,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!$this->scopeMatcher->isBackendRequest($request)) {
            throw new AccessDeniedHttpException('You are not in a Contao backend scope.');
        }

        /** @var string|null $action */
        $action = $request->request->get('action');
        $id = (int) $request->request->get('id');
        $oldIndex = (int) $request->request->get('oldIndex');
        $newIndex = (int) $request->request->get('newIndex');

        $table = $this->tableNameExtractor->getTableNameFromAction($action);

        if (null !== $table) {
            $this->sorter->sort($table, $id, $oldIndex, $newIndex);
        }

        return new JsonResponse();
    }
}
