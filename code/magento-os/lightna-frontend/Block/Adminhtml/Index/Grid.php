<?php

declare(strict_types=1);

namespace Lightna\Frontend\Block\Adminhtml\Index;

use Magento\Backend\Block\Widget\Grid\Container;

class Grid extends Container
{
    protected function _construct()
    {
        $this->_controller = 'index';
        $this->_headerText = __('Lightna Index');
        parent::_construct();
        $this->buttonList->remove('add');

        $this->buttonList->add(
            'lightna_index_invalidate_all',
            [
                'label' => __('Invalidate All Indices'),
                'onclick' => 'setLocation(\'' . $this->getUrl('lightna/index/invalidateAll') . '\')',
                'class' => 'primary'
            ]
        );
    }
}
