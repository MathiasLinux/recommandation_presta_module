<?php

class Product extends ProductCore
{
    public function addAccessoriesToProduct($accessories)
    {
        foreach ($accessories as $id_accessory) {
            $accessory = new Product($id_accessory);
            if ($accessory->id) {
                $this->addAccessory($accessory);
            }
        }
    }

    public function addAccessory($accessory)
    {
        // Add the accessory to the product
        $accessories = $this->getAccessories($this->id_lang);
        $accessories[] = $accessory;
        $this->setAccessories($this->id_lang, $accessories);
    }
}