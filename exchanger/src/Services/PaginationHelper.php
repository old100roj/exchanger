<?php

namespace App\Services;

use App\Structures\PageItem;
use App\Structures\PaginatedData;

class PaginationHelper
{
    public const RECORDS_PER_PAGE = 5;
    public const VISIBLE_PAGES_RADIUS = 5; // basically it's (pagination center width - 1) / 2

    /**
     * @return int
     */
    public static function getLimit(): int
    {
        return self::RECORDS_PER_PAGE;
    }

    /**
     * @param int $page
     * @return int
     */
    public static function getOffset(int $page): int
    {
        if ($page <= 1) {
            return 0;
        }

        return (int)(($page - 1) * self::RECORDS_PER_PAGE);
    }

    /**
     * @param int $recordsNumber
     * @return int
     */
    public static function getLastPage(int $recordsNumber): int
    {
        return (int)ceil($recordsNumber / self::RECORDS_PER_PAGE);
    }

    /**
     * @param PaginatedData $paginatedData
     * @param string $baseUri
     * @return PageItem[]
     */
    public static function generatePaginationBlock(PaginatedData $paginatedData, string $baseUri): array
    {
        /** @var PageItem[] $paginationBlock */
        $paginationBlock = [];
        $first = $paginatedData->firstPage;
        $last = $paginatedData->lastPage;
        $current = $paginatedData->currentPage;
        $hasPages = $last > $first;
        $prevPage = $current - 1;
        $nextPage = $current + 1;
        $isFirst = $current == $first;
        $isLast = $current == $last;
        $diapason = self::VISIBLE_PAGES_RADIUS;
        $href = '#';
        $dots = '...';
        self::addPageItem($paginationBlock, '<<', $baseUri . $prevPage, $isFirst, $hasPages);
        self::addPageItem($paginationBlock, '' . $first, $baseUri . $first, $isFirst, $hasPages && $current > $first);
        self::addPageItem($paginationBlock, $dots, $href, true, $hasPages && ($prevPage - $diapason) > $first);

        for ($i = -1 * $diapason; $i <= $diapason; $i++) {
            $number = $current + $i;
            $display = ($i < 0) ? $number > $first : $number < $last;
            self::addPageItem($paginationBlock, '' . $number, $baseUri . $number, $i == 0, $hasPages && $display);
        }

        self::addPageItem($paginationBlock, $dots, $href, true, $hasPages && ($nextPage + $diapason) < $last);
        self::addPageItem($paginationBlock, '' . $last, $baseUri . $last, $isLast, $hasPages);
        self::addPageItem($paginationBlock, '>>', $baseUri . $nextPage, $isLast, $hasPages);

        return $paginationBlock;
    }

    /**
     * @param string $page
     * @param int $recordsPerPage
     * @return string
     */
    public static function getPageToRedirect(string $page, int $recordsPerPage = self::RECORDS_PER_PAGE): string
    {
        if ($page === '') {
            $paginatedData = new PaginatedData();

            return '' . $paginatedData->firstPage;
        }

        if ($recordsPerPage < 1) {
            $page = '' . ((int)$page - 1);
        }

        return $page;
    }

    /**
     * @param array $array
     * @param string $text
     * @param string $href
     * @param bool $disabled
     * @param bool $display
     */
    private static function addPageItem(
        array &$array,
        string $text,
        string $href,
        bool $disabled,
        bool $display
    ): void {
        $pageItem = new PageItem($text, $href, $disabled, $display);
        $array[] = $pageItem;
    }
}
