<?php

class Product extends ProductCore
{
    public function addAccessoriesToProduct($accessories, $productID)
    {
        var_dump($accessories);
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
        $accessories[] = $accessory;
        var_dump($accessories);
        var_dump($productID);
        $this->setWsAccessories($accessories);
    }
}