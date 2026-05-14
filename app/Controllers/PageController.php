<?php
/**
 * Page Controller
 * Handles custom CMS pages display
 */

class PageController extends BaseController
{
    private CustomPageService $pageService;

    public function __construct()
    {
        $this->pageService = new CustomPageService(new CustomPage());
    }

    /**
     * GET /page/{slug} - Display custom page by slug
     */
    public function show(string $slug): void
    {
        $page = $this->pageService->getPage($slug);

        if (!$page) {
            $this->notFound();
            return;
        }

        $this->currentPage = 'page';
        $this->render('pages/custom', [
            'pageTitle' => $page['title'],
            'currentPage' => $this->currentPage,
            'page' => $page
        ]);
    }

    /**
     * GET /pages - List all published custom pages
     */
    public function index(): void
    {
        $pages = $this->pageService->getPublishedPages();
        $this->currentPage = 'pages';
        $this->render('pages/list', [
            'pageTitle' => 'Pages',
            'currentPage' => $this->currentPage,
            'pages' => $pages
        ]);
    }
}
