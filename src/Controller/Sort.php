<?php

declare(strict_types=1);

namespace Oneup\ContaoBackendSortableListViewsBundle\Controller;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Oneup\ContaoBackendSortableListViewsBundle\Service\Sorter;
use Oneup\ContaoBackendSortableListViewsBundle\Service\TableNameExtractor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class Sort
{
    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly TableNameExtractor $tableNameExtractor,
        private readonly Sorter $sorter,
        private readonly ContaoFramework $contaoFramework,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!$this->scopeMatcher->isBackendRequest($request)) {
            throw new AccessDeniedHttpException('You are not in a Contao backend scope.');
        }

        $action = $request->request->getString('action');
        $id = $request->request->getInt('id');
        $oldIndex = $request->request->getInt('oldIndex');
        $newIndex = $request->request->getInt('newIndex');

        $table = $this->tableNameExtractor->getTableNameFromAction($action);

        if (null === $table) {
            return new JsonResponse();
        }

        if (!$this->authorizationChecker->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, $action)) {
            throw new AccessDeniedHttpException(\sprintf('Not allowed to access the module "%s".', $action));
        }

        $this->contaoFramework->initialize();
        $this->contaoFramework->getAdapter(Controller::class)->loadDataContainer($table);

        if (!($GLOBALS['TL_DCA'][$table]['list']['sorting']['sortableListView'] ?? false)) {
            throw new AccessDeniedHttpException(\sprintf('The table "%s" is not a sortable list view.', $table));
        }

        $record = $this->sorter->findRecord($table, $id);

        if (null === $record) {
            return new JsonResponse();
        }

        $newRecord = ['sorting' => $this->sorter->getNewSorting($newIndex)];

        if (!$this->authorizationChecker->isGranted(ContaoCorePermissions::DC_PREFIX . $table, new UpdateAction($table, $record, $newRecord))) {
            throw new AccessDeniedHttpException(\sprintf('Not allowed to sort ID %s in table "%s".', $id, $table));
        }

        $this->sorter->sort($table, $id, $oldIndex, $newIndex);

        return new JsonResponse();
    }
}
