<?php
namespace Chili\Purchasedat\Block\Head;
class Head extends \Magento\Framework\View\Element\Template
{
    public $assetRepository;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        \Magento\Framework\View\Asset\Repository $assetRepository
    )
    {
        // Get the asset repository to get URL of our assets
        $this->assetRepository = $assetRepository;
        return parent::__construct($context, $data);
    }
}