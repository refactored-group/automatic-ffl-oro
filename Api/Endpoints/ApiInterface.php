<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Api\Endpoints;

interface ApiInterface
{
    /**
     * Constructs the URL to contact the specified Acumatica endpoint
     * @return string
     */
    public function constructEndpoint(): string;
}
