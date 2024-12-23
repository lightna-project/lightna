<?php

declare(strict_types=1);

namespace Lightna\Frontend\Block\Adminhtml\Index\Column;

use Magento\Backend\Block\Widget\Grid\Column;

class Status extends Column
{
    /** @noinspection PhpUnused */
    public function getFrameCallback(): array
    {
        return [$this, 'decorateStatus'];
    }

    public function decorateStatus($value, $row): string
    {
        $class = $row->getStatus() ? 'grid-severity-notice' : 'grid-severity-minor';

        return "<span class=\"$class\"><span>" . __($value) . "</span></span>";
    }
}
