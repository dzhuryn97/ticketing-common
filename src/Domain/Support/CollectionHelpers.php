<?php

namespace Ticketing\Common\Domain\Support;

use Doctrine\Common\Collections\Collection;

class CollectionHelpers
{
    public static function isCollectionsEqual(Collection $collection1, Collection $collection2, callable $comparisonFunction): bool
    {
        if($collection1->count() !== $collection2->count()){
            return false;
        }
        foreach ($collection1 as $item1) {

            $isCollection2ContainItem1 = $collection2->exists(function ($key, $item2) use($item1,$comparisonFunction){
                return $comparisonFunction($item1,$item2);
            });

            if(!$isCollection2ContainItem1){
                return false;
            }
        }

        return true;
    }
}