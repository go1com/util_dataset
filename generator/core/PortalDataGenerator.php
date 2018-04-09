<?php

namespace go1\util_dataset\generator\core;

use go1\util\portal\PortalHelper;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util_dataset\generator\CoreDataGeneratorTrait;

class PortalDataGenerator
{
    # Portal › Default
    # ---------------------
    public $portalId;
    public $portalName       = 'qa.mygo1.com';
    public $portalStatus     = 1;
    public $portalIsPrimary  = 1;
    public $portalVersion    = PortalHelper::STABLE_VERSION;
    public $portalPublicKey  = '9718b154-69d0-463c-9425-bb42d401d595';
    public $portalPrivateKey = 'c37cfacc-023c-4e98-a33e-d147b714c1a4';
    public $portalData       = [];
    public $portalRoleAdminId;

    # Portal › Content provider
    # ---------------------
    public $portalContentProviderId;
    public $portalContentProviderName       = 'provider.mygo1.com';
    public $portalContentProviderStatus     = 1;
    public $portalContentProviderIsPrimary  = 1;
    public $portalContentProviderVersion    = PortalHelper::STABLE_VERSION;
    public $portalContentProviderPublicKey  = '9ab46fcf-8ce7-4149-bfd1-35d367feeb05';
    public $portalContentProviderPrivateKey = 'da0c1f05-99ff-452d-99b9-c98f1a934b85';
    public $portalContentProviderData       = [];
    public $portalContentProviderRoleAdminId;

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
     * @param CoreDataGeneratorTrait $trait
     */
    private function doGenerate($trait)
    {
        $api = new class
        {
            use PortalMockTrait;
            use UserMockTrait;
        };

        # Portal › Default
        # ---------------------
        $trait->portalId = $api->createPortal($trait->go1, [
            'title'      => $trait->portalName,
            'status'     => $trait->portalStatus,
            'is_primary' => $trait->portalIsPrimary,
            'version'    => $trait->portalVersion,
            'data'       => $trait->portalData,
        ]);

        $api->createPortalPublicKey($trait->go1, ['instance' => $this->portalName]);
        $api->createPortalPrivateKey($trait->go1, ['instance' => $this->portalName]);
        $trait->portalRoleAdminId = $api->createPortalAdminRole($trait->go1, ['instance' => $this->portalName]);

        # Portal › Content provider
        # ---------------------
        $trait->portalContentProviderId = $api->createPortal($trait->go1, [
            'title'      => $trait->portalContentProviderName,
            'status'     => $trait->portalContentProviderStatus,
            'is_primary' => $trait->portalContentProviderIsPrimary,
            'version'    => $trait->portalContentProviderVersion,
            'data'       => $trait->portalContentProviderData,
        ]);

        $api->createPortalPublicKey($trait->go1, ['instance' => $trait->portalContentProviderName]);
        $api->createPortalPrivateKey($trait->go1, ['instance' => $trait->portalContentProviderName]);
        $trait->portalContentProviderRoleAdminId = $api->createPortalAdminRole($trait->go1, ['instance' => $trait->portalContentProviderName]);
    }
}
