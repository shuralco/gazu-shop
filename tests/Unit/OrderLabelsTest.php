<?php

namespace Tests\Unit;

use App\Support\OrderLabels;
use PHPUnit\Framework\TestCase;

class OrderLabelsTest extends TestCase
{
    public function test_payment_labels_cover_all_checkout_methods(): void
    {
        // усі способи з форми checkout + 1-клік
        foreach (['card', 'applepay', 'cod', 'invoice', 'manager_call'] as $m) {
            $label = OrderLabels::payment($m);
            $this->assertNotSame(ucfirst($m), $label, "payment '{$m}' має людську назву");
            $this->assertNotEmpty($label);
        }
    }

    public function test_shipping_labels_cover_all_methods(): void
    {
        foreach (['novaposhta', 'ukrposhta', 'pickup', 'manager_call'] as $m) {
            $label = OrderLabels::shipping($m);
            $this->assertNotSame(ucfirst($m), $label, "shipping '{$m}' має людську назву");
        }
    }

    public function test_unknown_keys_degrade_gracefully(): void
    {
        $this->assertSame('—', OrderLabels::payment(null));
        $this->assertSame('—', OrderLabels::shipping(null));
        $this->assertSame('Custom', OrderLabels::payment('custom'));
        $this->assertNull(OrderLabels::shippingType(null));
        $this->assertNull(OrderLabels::shippingType('unknown'));
    }

    public function test_payment_status_labels(): void
    {
        $this->assertSame('Сплачено', OrderLabels::paymentStatus('paid'));
        $this->assertSame('Очікує оплати', OrderLabels::paymentStatus('pending'));
        $this->assertSame('Очікує оплати', OrderLabels::paymentStatus(null)); // безпечний дефолт
    }

    public function test_shipping_type_labels(): void
    {
        $this->assertSame('Відділення', OrderLabels::shippingType('branch'));
        $this->assertSame('Поштомат', OrderLabels::shippingType('postomat'));
        $this->assertSame('Курʼєр НП', OrderLabels::shippingType('np_courier'));
    }
}
