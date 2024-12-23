<?php

declare(strict_types=1);

namespace Lightna\Demo\Observer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class BlockHtmlAfter implements ObserverInterface
{
    protected Observer $observer;
    protected bool $disabledLane;

    public function __construct(
        Http $request,
    ) {
        $this->disabledLane = !is_null($request->getParam('disable_lane'));
    }

    public function execute(Observer $observer): void
    {
        $this->observer = $observer;
        if ($this->disabledLane) {
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
