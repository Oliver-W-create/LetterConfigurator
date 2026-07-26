<?php
/* M4.6 – stabilized server-side configurator validation and cart transfer */
class OliLetterConfiguratorShoppingCart extends OliLetterConfiguratorShoppingCart_parent
{
    public function add_cart($products_id, $qty = '1', $attributes = '', $notify = true, $p_products_properties_combis_id = 0)
    {
        $configuration = $this->buildConfiguration((int)xtc_get_prid($products_id));
        if (isset($_POST['oli_lc_product_template_id']) && $configuration === null) {
            $_SESSION['oli_lc_cart_error'] = 'Die Produktkonfiguration ist unvollständig oder nicht mehr gültig.';
            return false;
        }

        $result = parent::add_cart($products_id, $qty, $attributes, $notify, $p_products_properties_combis_id);
        if ($configuration !== null) {
            $cartId = (string)($_SESSION['new_products_id_in_cart'] ?? '');
            if ($cartId !== '' && isset($this->contents[$cartId])) {
                $this->contents[$cartId]['oli_lc'] = $configuration;
                // parent::add_cart() calculates the cart before the configurator data is attached.
                // Recalculate once more so subtotal, total and tax use the configured price.
                $this->calculate();
            }
        }
        return $result;
    }

    public function get_products()
    {
        global $xtPrice;
        $products = parent::get_products();
        if (!is_array($products)) {
            return $products;
        }
        foreach ($products as &$product) {
            $id = (string)$product['id'];
            if (!empty($this->contents[$id]['oli_lc'])) {
                $config = $this->contents[$id]['oli_lc'];
                $price = $this->customerPrice((float)$config['net_price'], (int)$product['tax_class_id']);
                $product['price'] = $price;
                $product['final_price'] = $price;
                $product['oli_lc'] = $config;
            }
        }
        unset($product);
        return $products;
    }

    /**
     * Returns the merchandise subtotal used by Gambio's ot_subtotal module.
     *
     * The native implementation rebuilds the subtotal from the product master
     * price. Configurable products intentionally use a master price of 0.00,
     * so their validated configurator price must be read from get_products().
     * Shipping and all other order-total modules remain untouched.
     */
    public function show_total()
    {
        $subtotal = 0.0;
        $products = $this->get_products();

        if (!is_array($products)) {
            return parent::show_total();
        }

        foreach ($products as $product) {
            $quantity = (float)($product['quantity'] ?? $product['qty'] ?? 0);
            $unitPrice = (float)($product['final_price'] ?? $product['price'] ?? 0);
            $subtotal += $unitPrice * $quantity;
        }

        return round($subtotal, 2);
    }

    public function calculate()
    {
        global $xtPrice;

        $this->total = 0;
        $this->weight = 0;
        $this->tax = [];

        if (!is_array($this->contents)) {
            return 0;
        }

        foreach ($this->contents as $productsId => $item) {
            $qty = (float)($item['qty'] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $query = xtc_db_query(
                "SELECT products_id, products_price, products_discount_allowed, products_tax_class_id, products_weight " .
                "FROM " . TABLE_PRODUCTS . " WHERE products_id='" . xtc_db_input(xtc_get_prid($productsId)) . "' LIMIT 1"
            );
            $product = xtc_db_fetch_array($query);
            if (!$product) {
                continue;
            }

            $taxClassId = (int)$product['products_tax_class_id'];
            if (!empty($item['oli_lc'])) {
                // Use the server-side validated configurator price as unit price.
                $productsPrice = $this->customerPrice((float)$item['oli_lc']['net_price'], $taxClassId);
            } else {
                // Keep the native Gambio price calculation for ordinary products.
                $productsPrice = (float)$xtPrice->xtcGetPrice(
                    $productsId,
                    false,
                    $qty,
                    $taxClassId,
                    (float)$product['products_price'],
                    0,
                    0,
                    true,
                    true
                );
            }

            $this->total += $productsPrice * $qty;

            $propertiesWeight = (float)$this->properties_weight($productsId, $product['products_weight']);
            if ($propertiesWeight == 0.0) {
                $propertiesWeight = (float)$product['products_weight'];
            }
            $this->weight += $qty * $propertiesWeight;

            $attributePrice = 0.0;
            if (isset($item['attributes']) && is_array($item['attributes'])) {
                foreach ($item['attributes'] as $option => $value) {
                    $values = $xtPrice->xtcGetOptionPrice($product['products_id'], $option, $value);
                    $optionPrice = (float)($values['price'] ?? 0);
                    $optionWeight = (float)($values['weight'] ?? 0);
                    $attributePrice += $optionPrice;
                    $this->total += $optionPrice * $qty;
                    $this->weight += $optionWeight * $qty;
                }
            }

            if ($taxClassId !== 0) {
                $discountEnabled = (int)($_SESSION['customers_status']['customers_status_ot_discount_flag'] ?? 0) === 1;
                $discount = (float)($_SESSION['customers_status']['customers_status_ot_discount'] ?? 0);
                $productsPriceTax = $discountEnabled ? $productsPrice - ($productsPrice / 100 * $discount) : $productsPrice;
                $attributePriceTax = $discountEnabled ? $attributePrice - ($attributePrice / 100 * $discount) : $attributePrice;
                $rate = (float)($xtPrice->TAX[$taxClassId] ?? 0);

                $this->tax[$taxClassId] = $this->tax[$taxClassId] ?? ['value' => 0];

                if (($_SESSION['customers_status']['customers_status_show_price_tax'] ?? '0') === '1') {
                    $this->tax[$taxClassId]['value'] += ((($productsPriceTax + $attributePriceTax) / (100 + $rate)) * $rate) * $qty;
                    $this->tax[$taxClassId]['desc'] = sprintf(TAX_INFO_INCL, $rate . '%');
                } elseif (($_SESSION['customers_status']['customers_status_add_tax_ot'] ?? '0') === '1') {
                    $taxValue = (($productsPriceTax + $attributePriceTax) / 100) * $rate * $qty;
                    $this->tax[$taxClassId]['value'] += $taxValue;
                    $this->tax[$taxClassId]['desc'] = sprintf(TAX_INFO_EXCL, $rate . '%');
                    $this->total += $taxValue;
                }
            }

            if ($qty != (int)$qty) {
                foreach ($this->tax as $taxId => $taxData) {
                    $this->tax[$taxId]['value'] = round((float)$taxData['value'], 2);
                }
                $this->total = round($this->total, 2);
            }
        }

        return $this->total;
    }

    private function customerPrice(float $netPrice, int $taxClassId): float
    {
        global $xtPrice;
        $rate = (float)($xtPrice->TAX[$taxClassId] ?? 0);
        if (($_SESSION['customers_status']['customers_status_show_price_tax'] ?? '0') === '1') {
            return (float)$xtPrice->xtcAddTax($netPrice, $rate, true);
        }
        return (float)$xtPrice->xtcCalculateCurr($netPrice);
    }

    private function buildConfiguration(int $productsId): ?array
    {
        if (!isset($_POST['oli_lc_product_template_id'])) {
            return null;
        }
        $templateId = (int)$_POST['oli_lc_product_template_id'];
        $materialId = (int)($_POST['oli_lc_material'] ?? 0);
        $methodId = (int)($_POST['oli_lc_method'] ?? 0);
        $colorId = (int)($_POST['oli_lc_color'] ?? 0);
        $thicknessId = (int)($_POST['oli_lc_thickness'] ?? 0);
        $text = trim((string)($_POST['oli_lc_text'] ?? ''));
        $width = (float)str_replace(',', '.', (string)($_POST['oli_lc_width'] ?? 0));
        $height = (float)str_replace(',', '.', (string)($_POST['oli_lc_height'] ?? 0));
        $textLength = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        $validDimensions = is_finite($width) && is_finite($height)
            && $width >= 1 && $width <= 100000
            && $height >= 1 && $height <= 100000;
        if (!$templateId || !$materialId || !$methodId || !$colorId || !$thicknessId
            || $text === '' || $textLength > 255 || !$validDimensions) {
            return null;
        }

        $sql = "SELECT pt.price_profile_id, pt.color_mode, pt.thickness_mode, m.name material_name, pm.name method_name, c.name color_name, " .
               "t.thickness_min_mm, t.thickness_max_mm, pm.range_mode " .
               "FROM oli_lc_product_assignments a " .
               "JOIN oli_lc_product_templates pt ON pt.product_template_id=a.product_template_id AND pt.is_active=1 " .
               "JOIN oli_lc_product_template_materials ptm ON ptm.product_template_id=pt.product_template_id AND ptm.material_id={$materialId} " .
               "JOIN oli_lc_materials m ON m.material_id=ptm.material_id AND m.is_active=1 " .
               "JOIN oli_lc_product_template_production_methods ptpm ON ptpm.product_template_id=pt.product_template_id AND ptpm.production_method_id={$methodId} " .
               "JOIN oli_lc_production_methods pm ON pm.production_method_id=ptpm.production_method_id AND pm.is_active=1 " .
               "JOIN oli_lc_colors c ON c.color_id={$colorId} AND c.material_id={$materialId} AND c.is_active=1 " .
               "JOIN oli_lc_thicknesses t ON t.thickness_id={$thicknessId} AND t.material_id={$materialId} AND t.production_method=pm.method_key AND t.is_active=1 " .
               "WHERE a.products_id={$productsId} AND a.product_template_id={$templateId} AND a.is_active=1 LIMIT 1";
        $result = xtc_db_query($sql);
        if (!$row = xtc_db_fetch_array($result)) {
            return null;
        }
        if ($row['color_mode'] === 'selected' && !$this->relationExists('oli_lc_product_template_colors', $templateId, 'color_id', $colorId)) return null;
        if ($row['thickness_mode'] === 'selected' && !$this->relationExists('oli_lc_product_template_thicknesses', $templateId, 'thickness_id', $thicknessId)) return null;

        $profileResult = xtc_db_query("SELECT configuration_json FROM oli_lc_price_profiles WHERE price_profile_id=" . (int)$row['price_profile_id'] . " AND is_active=1 LIMIT 1");
        if (!$profile = xtc_db_fetch_array($profileResult)) return null;
        $cfg = json_decode((string)$profile['configuration_json'], true);
        if (!is_array($cfg) || (int)($cfg['material_id'] ?? 0) !== $materialId) return null;
        $methodResult = xtc_db_query("SELECT method_key FROM oli_lc_production_methods WHERE production_method_id={$methodId} LIMIT 1");
        $method = xtc_db_fetch_array($methodResult);
        if (!$method || (string)($cfg['production_method'] ?? '') !== (string)$method['method_key']) return null;

        $area = ($width * $height) / 1000000;
        $material = $area * (float)($cfg['area_price_per_m2'] ?? 0) * (1 + (float)($cfg['waste_percent'] ?? 0) / 100);
        $contour = (2 * ($width + $height)) * (float)($cfg['contour_price_per_mm'] ?? 0);
        $characters = mb_strlen(preg_replace('/\s+/u', '', $text), 'UTF-8') * (float)($cfg['price_per_character'] ?? 0);
        $net = max($material + $contour + $characters + (float)($cfg['fixed_price'] ?? 0) + (float)($cfg['setup_fee'] ?? 0), (float)($cfg['minimum_price'] ?? 0));
        $min = rtrim(rtrim(number_format((float)$row['thickness_min_mm'], 3, '.', ''), '0'), '.');
        $max = rtrim(rtrim(number_format((float)$row['thickness_max_mm'], 3, '.', ''), '0'), '.');
        $thickness = ($row['range_mode'] === 'single' || $min === $max) ? $min . ' mm' : $min . '–' . $max . ' mm';

        return [
            'template_id'=>$templateId, 'text'=>$text, 'width_mm'=>$width, 'height_mm'=>$height,
            'material_id'=>$materialId, 'material_name'=>$row['material_name'],
            'production_method_id'=>$methodId, 'production_method_name'=>$row['method_name'],
            'color_id'=>$colorId, 'color_name'=>$row['color_name'],
            'thickness_id'=>$thicknessId, 'thickness_name'=>$thickness,
            'net_price'=>round($net, 2)
        ];
    }

    private function relationExists(string $table, int $templateId, string $field, int $value): bool
    {
        $result = xtc_db_query("SELECT 1 FROM `{$table}` WHERE product_template_id={$templateId} AND `{$field}`={$value} LIMIT 1");
        return xtc_db_num_rows($result) > 0;
    }
}
