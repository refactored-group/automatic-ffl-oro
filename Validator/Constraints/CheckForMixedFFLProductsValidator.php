<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Validator\Constraints;

use Oro\Bundle\InventoryBundle\Validator\QuantityToOrderValidatorService;
use Oro\Bundle\ProductBundle\Model\QuickAddRowCollection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Validates that there is no mixed products when attempting to start checkout process.
 */
class CheckForMixedFFLProductsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof QuickAddRowCollection) {
            throw new UnexpectedValueException(
                $value,
                sprintf('%s', QuickAddRowCollection::class)
            );
        }

        if (!$constraint instanceof CheckForMixedFFLProducts) {
            throw new UnexpectedTypeException($constraint, CheckForMixedFFLProducts::class);
        }

        $hasFFLProducts = $this->hasFFLProducts($value);
        $hasMixedProductsInQuickOrder = $this->hasMixedProductsInQuickOrder($value);

        if($hasFFLProducts && $hasMixedProductsInQuickOrder) {
            $this->context
                ->buildViolation($constraint->hasMixedProducts)
                ->addViolation();
        }
    }

    public function hasFFLProducts(QuickAddRowCollection $quickAddRowCollection)
    {
        $numOfFFLProducts = 0;
        $numOfQuickAddRows = count($quickAddRowCollection);

        if ($numOfQuickAddRows > 0) {
            foreach ($quickAddRowCollection as $quickAddRow) {
                $product = $quickAddRow->getProduct();

                if ($product->getFflRequired()) {
                    $numOfFFLProducts++;
                }
            }
        }

        return $numOfFFLProducts > 0;
    }

    public function hasMixedProductsInQuickOrder(QuickAddRowCollection $quickAddRowCollection)
    {
        $numOfFFLProducts = 0;
        $numOfQuickAddRows = count($quickAddRowCollection);

        if (count($quickAddRowCollection) > 0) {
            foreach ($quickAddRowCollection as $quickAddRow) {
                $product = $quickAddRow->getProduct();

                if ($product->getFflRequired()) {
                    $numOfFFLProducts++;
                }
            }
        }

        return $numOfFFLProducts > 0 && $numOfFFLProducts < $numOfQuickAddRows;
    }
}
