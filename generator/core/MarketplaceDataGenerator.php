<?php

namespace go1\util_dataset\generator\core;

use go1\util\schema\mock\LoMockTrait;
use go1\util_dataset\generator\CoreDataGeneratorTrait;

class MarketplaceDataGenerator
{
    public $marketplaceCourseId;
    public $marketplaceCourseTitle            = 'First aid';
    public $marketplaceCourseStatus           = true;
    public $marketplaceCourseMarketplace      = true;
    public $marketplaceCourseShareWithPortals = [];

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

        $trait->marketplaceCourseId = $api->createCourse($trait->go1, [
            'instance_id' => $trait->portalContentProviderId,
            'title'       => $trait->marketplaceCourseTitle,
            'marketplace' => $trait->marketplaceCourseMarketplace,
            'status'      => $trait->marketplaceCourseStatus,
        ]);

        if ($trait->marketplaceCourseShareWithPortals) {
            foreach ($trait->marketplaceCourseShareWithPortals as $sharedWithPortalName) {
                $trait->go1->insert('gc_lo_group', [
                    'lo_id'       => $trait->marketplaceCourseId,
                    'instance_id' => $trait->go1->fetchColumn('SELECT id FROM gc_instance WHERE title = ?', [$sharedWithPortalName]),
                ]);
            }
        }
    }
}
