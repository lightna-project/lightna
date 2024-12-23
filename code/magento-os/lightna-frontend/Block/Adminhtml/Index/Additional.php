<?php

declare(strict_types=1);

namespace Lightna\Frontend\Block\Adminhtml\Index;

use Lightna\Engine\App\Query\Index\Changelog as LightnaChangelog;
use Lightna\Engine\App\Query\Index\Queue as LightnaQueue;
use Magento\Backend\Block\Template;

class Additional extends Template
{
    protected LightnaChangelog $lightnaChangelog;
    protected LightnaQueue $lightnaQueue;

    public function _construct(): void
    {
        parent::_construct();

        $this->lightnaChangelog = getobj(LightnaChangelog::class);
        $this->lightnaQueue = getobj(LightnaQueue::class);
    }

    public function _toHtml()
    {
        $changelogLabel = $this->escapeHtml(__('Changelog size:'));
        $changelogRows = $this->lightnaChangelog->getApproxRows();
        $queueLabel = $this->escapeHtml(__('Queue size:'));
        $queueRows = $this->lightnaQueue->getApproxRows();

        return <<<HTML
<div>
    <span>$changelogLabel</span> $changelogRows
</div>
<div>
    <span>$queueLabel</span> $queueRows
</div>
HTML;
    }
}
