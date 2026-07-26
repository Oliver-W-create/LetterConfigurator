<?php
/* M4.4 – show configuration in cart */
class OliLetterConfiguratorOrderDetailsCartThemeContentView extends OliLetterConfiguratorOrderDetailsCartThemeContentView_parent
{
    public function prepare_data()
    {
        parent::prepare_data();
        foreach ($this->products as $i => $product) {
            if (empty($product['oli_lc']) || !isset($this->moduleContent[$i])) continue;
            $c = $product['oli_lc'];
            $this->moduleContent[$i]['ATTRIBUTES'] = array_merge((array)($this->moduleContent[$i]['ATTRIBUTES'] ?? []), [
                ['NAME'=>'Text', 'VALUE_NAME'=>$c['text']],
                ['NAME'=>'Maße', 'VALUE_NAME'=>rtrim(rtrim(number_format($c['width_mm'], 2, ',', ''), '0'), ',') . ' × ' . rtrim(rtrim(number_format($c['height_mm'], 2, ',', ''), '0'), ',') . ' mm'],
                ['NAME'=>'Material', 'VALUE_NAME'=>$c['material_name']],
                ['NAME'=>'Fertigungsart', 'VALUE_NAME'=>$c['production_method_name']],
                ['NAME'=>'Farbe', 'VALUE_NAME'=>$c['color_name']],
                ['NAME'=>'Materialstärke', 'VALUE_NAME'=>$c['thickness_name']],
            ]);
        }
        $this->set_content_data('module_content', $this->moduleContent);

    }
}
