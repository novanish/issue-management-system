<?php if ($totalPages > 1) : ?>
    <nav aria-label="pagination" role="navigation">
        <ul class="pagination-list">
            <li>
                <?php if ($currentPage === 1) : ?>
                    <button class="pagination-link previous" aria-label="Go to previous page" disabled>
                        <svg xmlns=" http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                            <path d="m15 18-6-6 6-6"></path>
                        </svg>
                        <span>Previous</span>
                    </button>
                <?php else : ?>
                    <a class="pagination-link previous" aria-label="Go to previous page" href="<?= createPageLinks($currentPage - 1) ?>" data-partial>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                            <path d="m15 18-6-6 6-6"></path>
                        </svg>
                        <span>Previous</span>
                    </a>
                <?php endif; ?>
            </li>
            <?php foreach ($pages as $page) : ?>
                <?php if ($page === '...') : ?>
                    <li>
                        <span aria-hidden="true" class="more-pages">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                <circle cx="12" cy="12" r="1"></circle>
                                <circle cx="19" cy="12" r="1"></circle>
                                <circle cx="5" cy="12" r="1"></circle>
                            </svg>
                            <span class="sr-only">More pages</span>
                        </span>
                    </li>
                <?php else : ?>
                    <li>

                        <a class="pagination-link number<?= $page === $currentPage ? ' current-page' : '' ?>" href="<?= createPageLinks($page) ?>" data-partial>
                            <?= $page ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>


            <li>

                <?php if ($currentPage === $totalPages) : ?>
                    <button class="pagination-link next" aria-label="Go to next page" disabled>
                        <span>Next</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                            <path d="m9 18 6-6-6-6"></path>
                        </svg>
                    </button>
                <?php else : ?>
                    <a class="pagination-link next" aria-label="Go to next page" href="<?= createPageLinks($currentPage + 1) ?>" <?= $currentPage === $totalPages ? 'disabled' : '' ?> data-partial>
                        <span>Next</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                            <path d="m9 18 6-6-6-6"></path>
                        </svg>
                    </a>
                <?php endif; ?>
            </li>
        </ul>
        <div class="pagination-info">
            <span>Page <?= $currentPage ?> of <?= $totalPages ?></span>
        </div>
    </nav>
<?php endif; ?>