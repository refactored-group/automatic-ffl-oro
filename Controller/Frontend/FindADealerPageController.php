<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Controller\Frontend;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
}
