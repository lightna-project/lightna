<?php

declare(strict_types=1);

namespace Lightna\Magento\Backend\Data\Product;

use Lightna\Engine\Data\DataA;
use Lightna\Magento\Backend\Data\Content\Page as PageContent;
use Lightna\Magento\Backend\Data\Product;
use Lightna\Magento\Backend\Data\Product\Breadcrumbs\Breadcrumb;

class Breadcrumbs extends DataA
{
    /** @var Breadcrumb[] */
    public array $trail;

    protected PageContent $pageContent;
    protected Product $product;

    /** @noinspection PhpUnused */
    protected function defineTrail(): void
    {
        $this->trail = [];
        if (empty($this->product->categories)) {
            return;
        }

        // Take the last as the deepest one
        $this->trail = $this->build(end($this->product->categories));
    }

    protected function build(int $categoryId): array
    {
        $trail = [];
        $pointer = $categoryId;
        while ($category = ($this->pageContent->category[$pointer] ?? null)) {
            array_unshift($trail, newobj(Breadcrumb::class, [
                'name' => $category->name,
                'url' => '/' . $category->url,
            ]));
            $pointer = $category->parentId;
        }

        return $trail;
    }
}
