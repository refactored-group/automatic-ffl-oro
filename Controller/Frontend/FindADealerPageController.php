<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Controller\Frontend;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\CustomerBundle\Security\Token\AnonymousCustomerUserToken;
use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\OrderBundle\Entity\OrderAddress;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FindADealerPageController extends AbstractController
{
    /**
     * @Route("/show/find-a-dealer/dialog", name="ffl_frontend_show_find_a_dealer_dialog")
     * @Layout
     *
     * @param Request $request
     * @return array
     */
    public function showFindADealerDialogAction(Request $request)
    {
        /** @TODO: Future placeholder for rendering modal for Find A Dealer */
        return [
//            'data' => [
//            ],
        ];
    }

    /**
     * @Route("/dealers/list", name="ffl_frontend_dealers_list")
     * @Layout
     *
     * @param Request $request
     * @return array
     */
    public function getDealerListAction(Request $request)
    {
        /** @TODO: Future placeholder for getting a list of dealers */
        return [
//            'data' => [
//            ],
        ];
    }


    /**
     * @Route(
     *      "/ffl-checkout/update/{checkoutId}",
     *      name="ffl_frontend_update_checkout",
     *      requirements={"checkoutId"="\d+"},
     *      methods={"POST"}
     * )
     * @AclAncestor("oro_checkout_frontend_checkout")
     * @ParamConverter("checkout", class="OroCheckoutBundle:Checkout", options={"id" = "checkoutId"})
     *
     * @param Request $request
     * @param Checkout $checkout
     * @return JsonResponse
     */
    public function getUpdateCheckoutWithDealerShippingAddressAction(Request $request, Checkout $checkout): JsonResponse
    {
        $dealer = $request->get('dealer');
        $orderAddress = $this->createOrderAddress($dealer, $checkout->getBillingAddress());
        $checkout->setShippingAddress($orderAddress);

        $isSuccessful = false;

        try {
            $manager = $this->get(EntityManagerInterface::class);
            $manager->persist($checkout);
            $manager->flush($checkout);

            $isSuccessful = true;
        } catch (\Exception $e) {

        }

        return new JsonResponse(
            [
                'successful' => $isSuccessful,
            ]
        );
    }

    protected function createOrderAddress(array $address, OrderAddress $billingAddress): OrderAddress
    {
        $orderAddress = new OrderAddress();
        $orderAddress
            ->setOrganization($address['business_name'])
            ->setCountry($this->getCountryByIso2Code("US"))
            ->setCity($address['premise_city'])
            ->setRegion($this->getRegionByIso2Code($address['premise_state']))
            ->setStreet($address['premise_street'])
            ->setPostalCode($address['premise_zip'])
            ->setPhone($address['phone_number']);

        if ($this->isGuestCustomerUser())
        {
            $orderAddress->setLabel("FFL Dealer");
        } else {
            if ($billingAddress->getFirstName() && $billingAddress->getLastName()) {
                $name = $billingAddress->getFirstName() . " " . $billingAddress->getLastName();
                $orderAddress->setLabel($name);
            } elseif ($billingAddress->getOrganization()) {
                $orderAddress->setLabel($billingAddress->getOrganization());
            }

        }

        $manager = $this->get(EntityManagerInterface::class);
        $manager->persist($orderAddress);
        return $orderAddress;
    }

    protected function getCountryByIso2Code(string $iso2Code): ?Country
    {
        $manager = $this->get(EntityManagerInterface::class);
        return $manager->getReference('OroAddressBundle:Country', $iso2Code);
    }

    protected function getRegionByIso2Code(string $code): ?Region
    {
        return $this->getRepository(Region::class)->findOneBy(['code' => $code]);
    }

    private function isGuestCustomerUser(): bool
    {
        return $this->get(TokenStorageInterface::class)->getToken() instanceof AnonymousCustomerUserToken;
    }

    private function getRepository(string $className): ObjectRepository
    {
        $doctrine = $this->get(ManagerRegistry::class);
        return $doctrine->getManagerForClass($className)->getRepository($className);
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            TranslatorInterface::class,
            EntityManagerInterface::class,
            ManagerRegistry::class,
            TokenStorageInterface::class
        ]);
    }
}
