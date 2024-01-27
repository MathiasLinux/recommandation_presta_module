<?php

class Product extends ProductCore
{
    public function addAccessoriesToProduct($accessories, $productID)
    {
        foreach ($accessories as $id_accessory) {
            $accessory = new Product($id_accessory);
            if ($accessory->id) {
                $this->addAccessory($accessory, $productID);
            }
        }
    }

    public function addAccessory($accessory, $productID)
    {
        // Add the accessory to the product
        $accessories = $this->getAccessories($this->id_lang);
        // We verify that the accessory is not already present
        $request = 'SELECT `id_product_1` FROM `'._DB_PREFIX_.'accessory` WHERE `id_product_1` = '.(int)$productID.' AND `id_product_2` = '.(int)$accessory->id;
        $result = Db::getInstance()->getRow($request);
        if ($result) {
            return;
        }
        Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'accessory` (`id_product_1`, `id_product_2`) VALUES ('.(int)$productID.', '.(int)$accessory->id.')');
    }
}