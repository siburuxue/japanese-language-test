<?php

namespace App\Lib\Tool;

class PageTool
{

    public static function getDefaultPaginate(int $current, int $limit, int $total): string
    {
        $pageRange = 5;
        $pageCount = ceil($total / $limit);
        $proximity = floor($pageRange / 2);
        if ($pageCount < $current) {
            $current = $pageCount;
        }

        if ($pageRange > $pageCount) {
            $pageRange = $pageCount;
        }
        $delta = ceil($pageRange / 2);
        if ($current - $delta > $pageCount - $pageRange) {
            $pages = range($pageCount - $pageRange + 1, $pageCount);
        } else {
            if ($current - $delta < 0) {
                $delta = $current;
            }

            $offset = $current - $delta;
            $pages = range($offset + 1, $offset + $pageRange);
        }
        $startPage = $current - $proximity;
        $endPage = $current + $proximity;
        if ($startPage < 1) {
            $endPage = min($endPage + (1 - $startPage), $pageCount);
            $startPage = 1;
        }

        if ($endPage > $pageCount) {
            $startPage = max($startPage - ($endPage - $pageCount), 1);
            $endPage = $pageCount;
        }

        $pageHtml = "";
        if ($startPage > 1) {
            $pageHtml .= '<a class="paginate_button" data-page="1">1</a>';
            if ($startPage == 3) {
                $pageHtml .= '<a class="paginate_button" data-page="2">2</a>';
            } else {
                if ($startPage != 2) {
                    $pageHtml .= '<a class="paginate_button" data-page="next-group">&hellip;</a>';
                }
            }
        }

        foreach ($pages as $page) {
            if ($page != $current) {
                $pageHtml .= '<a class="paginate_button" data-page="' . $page . '">' . $page . '</a>';
            } else {
                $pageHtml .= '<a class="paginate_button current" data-page="' . $page . '">' . $page . '</a>';
            }
        }

        if ($pageCount > $endPage) {
            if ($pageCount > $endPage + 1) {
                if ($pageCount > $endPage + 2) {
                    $pageHtml .= '<a class="paginate_button disabled" data-page="next-group">&hellip;</a>';
                } else {
                    $pageHtml .= '<a class="paginate_button" data-page="' . ($pageCount - 1) . '">' . ($pageCount - 1) . '</a>';
                }
            }
            $pageHtml .= '<a class="paginate_button" data-page="' . $pageCount . '">' . $pageCount . '</a>';
        }

        if ($pageCount >= 1) {
            return
                '<div class="dataTables_paginate paging_simple_numbers">
                    共 ' . $total . ' 条&nbsp;&nbsp;&nbsp;
                    <select class="page-count" style="height:28px;">
                        <option value="10" ' . ($limit == 10 ? "selected" : "") . '>10条/页</option>
                        <option value="20" ' . ($limit == 20 ? "selected" : "") . '>20条/页</option>
                        <option value="50" ' . ($limit == 50 ? "selected" : "") . '>50条/页</option>
                        <option value="100" ' . ($limit == 100 ? "selected" : "") . '>100条/页</option>
                    </select>
                    <a class="paginate_button previous" data-page="1">First</a>
                    <a class="paginate_button previous" data-page="prev">Prev</a>
                    <span>
                          ' . $pageHtml . '
                    </span>
                    <a class="paginate_button next" data-page="next">Next</a>
                    <a class="paginate_button next last" data-page="' . $pageCount . '" data-total="' . $pageCount . '">Last</a>
                    跳转至&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" class="jump" style="width:50px;height:28px;">
                    <a class="paginate_button go next" data-page="go">Go</a>
                </div>';
        }
        return "";
    }
}