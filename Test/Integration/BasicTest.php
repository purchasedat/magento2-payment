<?
namespace PurchasedAt\Magento2Payment\Test\Integration;

use PurchasedAt\PurchaseOptions;

class ModelTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var PurchasedAt\Magento2Payment\Model\PurchasedatModel
	 */
	protected $model;

	/**
	 * @var PurchasedAt\Magento2Payment\Helper\Data
	 */
	protected $dataHelper;

	protected function setUp()
	{
		$objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
		$this->model = $objectManager->getObject('PurchasedAt\Magento2Payment\Model\PurchasedatModel');
		$this->dataHelper = $objectManager->getObject('PurchasedAt\Magento2Payment\Helper\Data');
	}

	public function testGetScript(){
		$cfg = array(
			"apiKey" => PURCHASEDAT_MAGENTTO2PAYMENT_APIKEY,
			"options" => new PurchaseOptions("test@example.com")
		);
		$script = $this->model->renderScript($cfg["apiKey"], $cfg["options"]);

		$this->assertRegExp('%^<script(.*?)http(.*?)purchased\.at/widget/(.*?)/js(.*?)</script>(.*?)purchased_at\.auto\({(.*?)"token":"(.+?)"(.*?)</script>%si', $script);
	}

	public function testDataHelper(){
		$number = $this->dataHelper->getNumberFormat(99);
		$this->assertTrue($number === "99.00");
	}

}