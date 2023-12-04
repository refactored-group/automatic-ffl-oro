<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Layout\DataProvider;

use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use RefactoredGroup\Bundle\AutomaticFFLBundle\Provider\AutomaticFFLIntegrationProvider;

/**
 * RefactoredGroup AutomaticFFLIntegrationProvider class
 */
class AutoFFLIntegrationProvider
{
    protected AutomaticFFLIntegrationProvider $provider;

    public function __construct(
        AutomaticFFLIntegrationProvider $provider
    ) {
        $this->provider = $provider;
    }

    public function isFFLEnabled()
    {
        return $this->provider->isFFLEnabled();
    }

    public function hasFFLProducts(Checkout $checkout)
    {
        $numOfFFLProducts = 0;
        $lineItems = $checkout->getLineItems();

        if (count($lineItems) > 0) {
            foreach ($lineItems as $lineItem) {
                $product = $lineItem->getProduct();

                if ($product->getFflRequired()) {
                    $numOfFFLProducts++;
                }
            }
        }

        return $numOfFFLProducts > 0;
    }

    public function getShoppingListFromCheckout($checkout)
    {
        $shoppingList = $checkout->getSource()->getShoppingList();

        return $shoppingList;
    }

    public function hasMixedProductsInShoppingList(ShoppingList $shoppingList)
    {
        $numOfFFLProducts = 0;
        $lineItems = $shoppingList->getLineItems();
        $numOfLineItems = count($lineItems);

        if (count($lineItems) > 0) {
            foreach ($lineItems as $lineItem) {
                $product = $lineItem->getProduct();

                if ($product->getFflRequired()) {
                    $numOfFFLProducts++;
                }
            }
        }

        return $numOfFFLProducts > 0 && $numOfFFLProducts < $numOfLineItems;
    }
}
