<?php
/**
 * M4.5.7 – make Gambio's order/order-total calculation aware of the
 * server-side validated configurator price stored in the cart session.
 */
class OliLetterConfiguratorOrder extends OliLetterConfiguratorOrder_parent
{
    public function __construct($order_id = '')
    {
        parent::__construct($order_id);

        // Stored orders already contain final prices in the database and must
        // never be recalculated from the current shopping-cart session.
        if (xtc_not_null($order_id)) {
            return;
        }

        $this->applyConfiguratorPrices();
    }

    private function applyConfiguratorPrices(): void
    {
        global $xtPrice;

        if (empty($_SESSION['cart']) || !is_object($_SESSION['cart']) || !is_array($this->products)) {
            return;
        }

        $cartProducts = $_SESSION['cart']->get_products();
        if (!is_array($cartProducts) || !$cartProducts) {
            return;
        }

        $cartProductsById = [];
        foreach ($cartProducts as $cartProduct) {
            if (!isset($cartProduct['id'])) {
                continue;
            }
            $cartProductsById[(string)$cartProduct['id']] = $cartProduct;
        }

        $hasConfiguratorProduct = false;
        foreach ($this->products as &$orderProduct) {
            $cartId = (string)($orderProduct['id'] ?? '');
            if ($cartId === '' || empty($cartProductsById[$cartId]['oli_lc'])) {
                continue;
            }

            $cartProduct = $cartProductsById[$cartId];
            $unitPrice = (float)($cartProduct['final_price'] ?? $cartProduct['price'] ?? 0);
            $quantity = (float)($orderProduct['qty'] ?? $cartProduct['quantity'] ?? 0);
            $linePrice = $unitPrice * $quantity;

            $orderProduct['price'] = $unitPrice;
            $orderProduct['price_formated'] = $xtPrice->xtcFormat($unitPrice, true);
            $orderProduct['final_price'] = $linePrice;
            $orderProduct['final_price_formated'] = $xtPrice->xtcFormat($linePrice, true);
            $orderProduct['oli_lc'] = $cartProduct['oli_lc'];
            $hasConfiguratorProduct = true;
        }
        unset($orderProduct);

        if (!$hasConfiguratorProduct) {
            return;
        }

        $subtotal = 0.0;
        $taxTotal = 0.0;
        $taxGroups = [];
        $showTax = (string)($_SESSION['customers_status']['customers_status_show_price_tax'] ?? '0') === '1';
        $addTaxToOrderTotal = (string)($_SESSION['customers_status']['customers_status_add_tax_ot'] ?? '0') === '1';
        $discountEnabled = (int)($_SESSION['customers_status']['customers_status_ot_discount_flag'] ?? 0) === 1;
        $discountPercent = (float)($_SESSION['customers_status']['customers_status_ot_discount'] ?? 0);

        foreach ($this->products as $product) {
            $linePrice = (float)($product['final_price'] ?? 0);
            $subtotal += $linePrice;

            $taxRate = (float)($product['tax'] ?? 0);
            if ($taxRate <= 0) {
                continue;
            }

            $taxableLinePrice = $discountEnabled
                ? $linePrice - ($linePrice / 100 * $discountPercent)
                : $linePrice;
            $taxDescription = (string)($product['tax_description'] ?? ($taxRate . '%'));

            if ($showTax) {
                $taxValue = ($taxableLinePrice / (100 + $taxRate)) * $taxRate;
                $groupKey = TAX_ADD_TAX . $taxDescription;
            } else {
                $taxValue = ($taxableLinePrice / 100) * $taxRate;
                $groupKey = TAX_NO_TAX . $taxDescription;
            }

            $taxTotal += $taxValue;
            $taxGroups[$groupKey] = ($taxGroups[$groupKey] ?? 0.0) + $taxValue;
        }

        $subtotal = round($subtotal, 2);
        $taxTotal = round($taxTotal, 2);
        foreach ($taxGroups as $key => $value) {
            $taxGroups[$key] = round($value, 2);
        }

        $shippingCost = (float)$xtPrice->xtcFormat($this->info['shipping_cost'] ?? 0, false, 0, true);
        $total = $subtotal + $shippingCost;
        if ($discountEnabled) {
            $total -= round($subtotal / 100 * $discountPercent, 2);
        }
        if (!$showTax && $addTaxToOrderTotal) {
            $total += $taxTotal;
        }

        $this->info['subtotal'] = $subtotal;
        $this->info['tax'] = $taxTotal;
        $this->info['tax_groups'] = $taxGroups;
        $this->info['total'] = round($total, 2);
    }
}
