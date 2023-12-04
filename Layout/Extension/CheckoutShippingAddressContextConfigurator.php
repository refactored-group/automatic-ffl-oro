<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Layout\Extension;

use Oro\Component\Layout\ContextConfiguratorInterface;
use Oro\Component\Layout\ContextInterface;
use RefactoredGroup\Bundle\AutomaticFFLBundle\Layout\DataProvider\AutoFFLIntegrationProvider;
use RefactoredGroup\Bundle\AutomaticFFLBundle\Provider\AutomaticFFLIntegrationProvider;
use Symfony\Component\HttpFoundation\RequestStack;

/** Add "is_ffl_enabled" to Checkout shipping address step context **/
class CheckoutShippingAddressContextConfigurator implements ContextConfiguratorInterface
{
    const IS_FFL_ENABLED_OPTION_NAME = 'is_ffl_enabled';
    const HAS_FFL_PRODUCTS_OPTION_NAME = 'has_ffl_products';
    const CHECKOUT_ROUTE = 'oro_checkout_frontend_checkout';

    protected RequestStack $requestStack;
    protected AutoFFLIntegrationProvider $provider;

    public function __construct(
        RequestStack $requestStack,
        AutoFFLIntegrationProvider $provider
    ) {
        $this->requestStack = $requestStack;
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function configureContext(ContextInterface $context)
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return;
        }

        $allowedRoutes = [self::CHECKOUT_ROUTE];
        if (!\in_array($request->attributes->get('_route'), $allowedRoutes, true)) {
            return;
        }

        // Adds bool check for FFL products in checkout's line items to context
        if ($context->data()->has('checkout')) {
            $checkout = $context->data()->get('checkout');
            $hasFFLProducts = $this->provider->hasFFLProducts($checkout);

            $context->getResolver()->setDefined(self::HAS_FFL_PRODUCTS_OPTION_NAME);
            $context->set(self::HAS_FFL_PRODUCTS_OPTION_NAME, $hasFFLProducts);
        }

        $isFFLEnabled = $this->provider->isFFLEnabled();
        // Adds check for FFL integration being enabled to context
        $context->getResolver()->setDefined(self::IS_FFL_ENABLED_OPTION_NAME);
        $context->set(self::IS_FFL_ENABLED_OPTION_NAME, $isFFLEnabled);
    }
}
