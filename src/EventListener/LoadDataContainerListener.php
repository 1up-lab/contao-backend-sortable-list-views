<?php

declare(strict_types=1);

namespace Oneup\ContaoBackendSortableListViewsBundle\EventListener;

use Contao\Controller;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Oneup\ContaoBackendSortableListViewsBundle\Service\Sorter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class LoadDataContainerListener
{
    public function __construct(
        private readonly ScopeMatcher $scopeMatcher,
        private readonly RequestStack $requestStack,
        private readonly ContaoFramework $contaoFramework,
        private readonly ContaoCsrfTokenManager $contaoCsrfToken,
        private readonly Environment $twig,
        private readonly RouterInterface $router,
        private readonly Sorter $sorter,
    ) {
    }

    public function __invoke(string $table): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request || !$this->scopeMatcher->isBackendRequest($request)) {
            return;
        }

        $GLOBALS['TL_MOOTOOLS'][] = $this->twig->render('@OneupContaoBackendSortableListViews/sortable_list_view.html.twig', [
            'token' => $this->contaoCsrfToken->getDefaultTokenValue(),
            'path' => $this->router->generate('oneup_sortable_list_views.sort'),
        ]);

        if (($GLOBALS['TL_DCA'][$table]['list']['sorting']['sortableListView'] ?? null) && !$request->get('act')) {
            $GLOBALS['TL_DCA'][$table]['list']['sorting']['flag'] = 1;
            $GLOBALS['TL_DCA'][$table]['list']['sorting']['mode'] = 1;
            $GLOBALS['TL_DCA'][$table]['list']['sorting']['fields'] = ['sorting'];

            $GLOBALS['TL_DCA'][$table]['list']['operations']['drag'] = [
                'href' => '#',
                'icon' => 'drag.svg',
                'attributes' => 'class="drag-handle" aria-hidden="true"',
            ];

            if ($request->get('do')) {
                $this->sorter->sortInitial($table);
            }
        }

        /** @var Controller $controllerAdapter */
        $controllerAdapter = $this->contaoFramework->getAdapter(Controller::class);

        if ($GLOBALS['TL_DCA'][$table]['list']['sorting']['sortableListView'] ?? null) {
            $GLOBALS['TL_JAVASCRIPT'][] = $controllerAdapter->addStaticUrlTo('bundles/oneupcontaobackendsortablelistviews/backend.min.js');
        }
    }
}
