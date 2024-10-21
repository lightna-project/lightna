<?php

declare(strict_types=1);

namespace Lightna\Demo\Observer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class BlockHtmlAfter implements ObserverInterface
{
    protected Observer $observer;

    public function __construct(
        protected Http $request,
    ) {
    }

    public function execute(Observer $observer): void
    {
        $this->observer = $observer;
        $disabledLane = !is_null($this->request->getParam('disable_lane'));
        if ($disabledLane) {
            if ($this->isBlock('cookie-status-check')) {
                $this->setBlockHtml(
                    blockhtml('#server-time-container') . $this->getBlockHtml()
                );
            } elseif ($this->isBlock('copyright')) {
                $this->setBlockHtml(
                    $this->getBlockHtml() . blockhtml('#server-time')
                );
            }
        }
    }

    protected function isBlock(string $name): bool
    {
        return $this->observer->getBlock()->getNameInLayout() === $name;
    }

    protected function setBlockHtml(string $html): void
    {
        $this->observer->getTransport()->setHtml($html);
    }

    protected function getBlockHtml(): string
    {
        return $this->observer->getTransport()->getHtml();
    }
}
