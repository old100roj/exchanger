<?php

namespace App\Tests\Services;

use App\Services\PaginationHelper;
use App\Structures\PageItem;
use App\Structures\PaginatedData;
use PHPUnit\Framework\TestCase;

class PaginationHelperTest extends TestCase
{
    private const BASE_URI = 'base/';

    public function testGetLimit(): void
    {
        $limit = PaginationHelper::getLimit();
        $this->assertEquals($limit, PaginationHelper::RECORDS_PER_PAGE);
    }

    public function testGetOffset(): void
    {
        $possiblePageNumbers = [1, 2, 3, 567];

        foreach ($possiblePageNumbers as $number) {
            $offset = PaginationHelper::getOffset($number);
            $this->assertEquals($offset, ($number - 1) * PaginationHelper::RECORDS_PER_PAGE);
        }
    }

    public function testGetOffsetIfIncorrectPage(): void
    {
        $possiblePageNumbers = [-234, -2, -1, 0];

        foreach ($possiblePageNumbers as $number) {
            $offset = PaginationHelper::getOffset($number);
            $this->assertEquals($offset, 0);
        }
    }

    public function testGetLastPage(): void
    {
        $pages = 5;
        $recordsNumber = $pages * PaginationHelper::RECORDS_PER_PAGE;
        $lastPage = PaginationHelper::getLastPage($recordsNumber);
        $this->assertEquals($lastPage, $pages);
        $recordsNumber++;
        $lastPage = PaginationHelper::getLastPage($recordsNumber);
        $this->assertEquals($lastPage, $pages + 1);
        $recordsNumber--;
        $recordsNumber += PaginationHelper::RECORDS_PER_PAGE;
        $lastPage = PaginationHelper::getLastPage($recordsNumber);
        $this->assertEquals($lastPage, $pages + 1);
        $recordsNumber++;
        $lastPage = PaginationHelper::getLastPage($recordsNumber);
        $this->assertEquals($lastPage, $pages + 2);
    }

    public function testGeneratePaginationBlockOnFirstPage(): void
    {
        $paginatedData = new PaginatedData();
        $paginatedData->lastPage = 7;
        $paginationBlock = PaginationHelper::generatePaginationBlock($paginatedData, self::BASE_URI);
        $expectedArray = [
            new PageItem('<<', self::BASE_URI . '0', true, true),
            new PageItem('1', self::BASE_URI . '1', true, false),
            new PageItem('...', '#', true, false),
            new PageItem('-4', self::BASE_URI . '-4', false, false),
            new PageItem('-3', self::BASE_URI . '-3', false, false),
            new PageItem('-2', self::BASE_URI . '-2', false, false),
            new PageItem('-1', self::BASE_URI . '-1', false, false),
            new PageItem('0', self::BASE_URI . '0', false, false),
            new PageItem('1', self::BASE_URI . '1', true, true),
            new PageItem('2', self::BASE_URI . '2', false, true),
            new PageItem('3', self::BASE_URI . '3', false, true),
            new PageItem('4', self::BASE_URI . '4', false, true),
            new PageItem('5', self::BASE_URI . '5', false, true),
            new PageItem('6', self::BASE_URI . '6', false, true),
            new PageItem('...', '#', true, false),
            new PageItem('7', self::BASE_URI . '7', false, true),
            new PageItem('>>', self::BASE_URI . '2', false, true),
        ];

        foreach ($expectedArray as $key => $value) {
            $this->assertEquals($expectedArray[$key], $paginationBlock[$key]);
        }
    }

    public function testGeneratePaginationBlockOnMiddle(): void
    {
        $paginatedData = new PaginatedData();
        $paginatedData->lastPage = 7;
        $paginatedData->currentPage = 4;
        $paginationBlock = PaginationHelper::generatePaginationBlock($paginatedData, self::BASE_URI);
        $expectedArray = [
            new PageItem('<<', self::BASE_URI . '3', false, true),
            new PageItem('1', self::BASE_URI . '1', false, true),
            new PageItem('...', '#', true, false),
            new PageItem('-1', self::BASE_URI . '-1', false, false),
            new PageItem('0', self::BASE_URI . '0', false, false),
            new PageItem('1', self::BASE_URI . '1', false, false),
            new PageItem('2', self::BASE_URI . '2', false, true),
            new PageItem('3', self::BASE_URI . '3', false, true),
            new PageItem('4', self::BASE_URI . '4', true, true),
            new PageItem('5', self::BASE_URI . '5', false, true),
            new PageItem('6', self::BASE_URI . '6', false, true),
            new PageItem('7', self::BASE_URI . '7', false, false),
            new PageItem('8', self::BASE_URI . '8', false, false),
            new PageItem('9', self::BASE_URI . '9', false, false),
            new PageItem('...', '#', true, false),
            new PageItem('7', self::BASE_URI . '7', false, true),
            new PageItem('>>', self::BASE_URI . '5', false, true),
        ];

        foreach ($expectedArray as $key => $value) {
            $this->assertEquals($expectedArray[$key], $paginationBlock[$key]);
        }
    }

    public function testGeneratePaginationBlockOnLastPage(): void
    {
        $paginatedData = new PaginatedData();
        $paginatedData->lastPage = 7;
        $paginatedData->currentPage = 7;
        $paginationBlock = PaginationHelper::generatePaginationBlock($paginatedData, self::BASE_URI);
        $expectedArray = [
            new PageItem('<<', self::BASE_URI . '6', false, true),
            new PageItem('1', self::BASE_URI . '1', false, true),
            new PageItem('...', '#', true, false),
            new PageItem('2', self::BASE_URI . '2', false, true),
            new PageItem('3', self::BASE_URI . '3', false, true),
            new PageItem('4', self::BASE_URI . '4', false, true),
            new PageItem('5', self::BASE_URI . '5', false, true),
            new PageItem('6', self::BASE_URI . '6', false, true),
            new PageItem('7', self::BASE_URI . '7', true, false),
            new PageItem('8', self::BASE_URI . '8', false, false),
            new PageItem('9', self::BASE_URI . '9', false, false),
            new PageItem('10', self::BASE_URI . '10', false, false),
            new PageItem('11', self::BASE_URI . '11', false, false),
            new PageItem('12', self::BASE_URI . '12', false, false),
            new PageItem('...', '#', true, false),
            new PageItem('7', self::BASE_URI . '7', true, true),
            new PageItem('>>', self::BASE_URI . '8', true, true),
        ];

        foreach ($expectedArray as $key => $value) {
            $this->assertEquals($expectedArray[$key], $paginationBlock[$key]);
        }
    }

    public function testGeneratePaginationBlockIfOnePage()
    {
        $paginatedData = new PaginatedData();
        $paginatedData->lastPage = 1;
        $paginationBlock = PaginationHelper::generatePaginationBlock($paginatedData, self::BASE_URI);

        foreach ($paginationBlock as $pageItem) {
            $this->assertFalse($pageItem->display);
        }
    }
}
