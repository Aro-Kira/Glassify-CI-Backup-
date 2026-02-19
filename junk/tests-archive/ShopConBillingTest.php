<?php
use PHPUnit\Framework\TestCase;

class ShopConBillingTest extends TestCase
{
    public function testBuildPaymongoBillingProducesExpectedStructure()
    {
        // Bootstrap CodeIgniter instance
        $CI =& get_instance();
        $shop = new ShopCon();

        // NOTE: This test assumes a test order with ID 1 exists in test DB.
        // Adapt order_id to a valid test fixture in your environment.
        $order_id = 1;

        $result = $shop->build_paymongo_billing($order_id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('paymongo_billing', $result);
        $pm = $result['paymongo_billing'];
        $this->assertArrayHasKey('address', $pm);
        $this->assertArrayHasKey('city', $pm['address']);
        $this->assertArrayHasKey('postal_code', $pm['address']);
        $this->assertEquals(2, strlen($pm['address']['country'])); // ISO code
    }
}
