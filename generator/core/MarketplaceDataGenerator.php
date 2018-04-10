<?php

namespace go1\util_dataset\generator\core;

use go1\util\schema\mock\LoMockTrait;
use go1\util_dataset\generator\CoreDataGeneratorTrait;

class MarketplaceDataGenerator
{
    public $marketplaceCourseId;
    public $marketplaceCourseTitle  = 'First aid';
    public $marketplaceCourseStatus = true;

    /**
     * @param CoreDataGeneratorTrait $trait
     * @param callable               $callback
     */
    public function generate(&$trait, callable $callback = null)
    {
        $trait::cloneProperties($this, $trait);
        $this->doGenerate($trait);
        $callback && call_user_func($callback);
    }

    /**
     * @param CoreDataGeneratorTrait|PortalDataGenerator $trait
     */
    private function doGenerate($trait)
    {
        $api = new class
        {
            use LoMockTrait;
        };

        $api->createCourse($trait->go1, [
            'instance_id' => $trait->portalContentProviderId,
            'title'       => $this->marketplaceCourseTitle,
            'marketplace' => true,
            'status'      => $this->marketplaceCourseStatus,
        ]);
    }
}
