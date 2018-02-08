<?php

namespace go1\util_dataset\generator;

use Doctrine\DBAL\Connection;
use go1\util\edge\EdgeTypes;
use go1\util\lo\LiTypes;
use go1\util\portal\PortalHelper;
use go1\util\schema\mock\InstanceMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\UserMockTrait;

trait CoreDataGeneratorTrait
{
    # Portal › Default
    # ---------------------
    protected $portalId;
    protected $portalName       = 'qa.mygo1.com';
    protected $portalStatus     = 1;
    protected $portalIsPrimary  = 1;
    protected $portalVersion    = PortalHelper::STABLE_VERSION;
    protected $portalPublicKey  = '9718b154-69d0-463c-9425-bb42d401d595';
    protected $portalPrivateKey = 'c37cfacc-023c-4e98-a33e-d147b714c1a4';
    protected $portalData       = [];
    protected $portalRoleAdminId;

    # Portal › Content provider
    # ---------------------
    protected $portalContentProviderId;
    protected $portalContentProviderName       = 'provider.mygo1.com';
    protected $portalContentProviderStatus     = 1;
    protected $portalContentProviderIsPrimary  = 1;
    protected $portalContentProviderVersion    = PortalHelper::STABLE_VERSION;
    protected $portalContentProviderPublicKey  = '9ab46fcf-8ce7-4149-bfd1-35d367feeb05';
    protected $portalContentProviderPrivateKey = 'da0c1f05-99ff-452d-99b9-c98f1a934b85';
    protected $portalContentProviderData       = [];
    protected $portalContentProviderRoleAdminId;

    # User › Portal admin
    # ---------------------
    protected $userAdminUuid = '1a24235e-30f6-49cb-9e5c-3aaeff046288';
    protected $userAdminId;
    protected $userAdminAccountId;
    protected $userAdminMail;
    protected $userAdminFirstName = 'Foo.PortalAdmin';
    protected $userAdminLastName  = 'Bar.PortalAdmin';

    # User › Manager
    # ---------------------
    protected $userManagerId;
    protected $userManagerAccountId;
    protected $userManagerMail;
    protected $userManagerFirstName = 'Foo.UserManager';
    protected $userManagerLastName  = 'Bar.UserManager';

    # User › Course assessor
    # ---------------------
    protected $userCourseAssessorId;
    protected $userCourseAssessorAccountId;
    protected $userCourseAssessorMail;
    protected $userCourseAssessorFirstName = 'Foo.CourseAssessor';
    protected $userCourseAssessorLastName  = 'Bar.CourseAssessor';

    # User › Enrolment assessor
    # ---------------------
    protected $userEnrolmentAssessorId;
    protected $userEnrolmentAssessorAccountId;
    protected $userEnrolmentAssessorMail;
    protected $userEnrolmentAssessorFirstName = 'Foo.EnrolmentAssessor';
    protected $userEnrolmentAssessorLastName  = 'Bar.EnrolmentAssessor';

    # User › Learner 1
    # ---------------------
    protected $userLearner1Uuid = '51eb3cc1-b65c-4278-94fa-62005e5df5d7';
    protected $userLearner1Id;
    protected $userLearner1AccountId;
    protected $userLearner1Mail;
    protected $userLearner1FirstName = 'Foo.Learner1';
    protected $userLearner1LastName  = 'Bar.Learner1';

    # User › Learner 2
    # ---------------------
    protected $userLearner2Uuid = 'bf650d4e-e33d-4cbb-be10-df6334e8120c';
    protected $userLearner2Id;
    protected $userLearner2AccountId;
    protected $userLearner2Mail;
    protected $userLearner2FirstName = 'Foo.Learner2';
    protected $userLearner2LastName  = 'Bar.Learner2';

    # Course: Making web 101
    #   Event:
    #       1. Understand the web in 4 hours.
    #       2. Web for everyone meet-up
    #   Modules:
    #       1. Basics of HTML
    #       2. Introduction to CSS
    # ---------------------
    protected $courseWebId;
    protected $courseTitle                              = 'Making web 101';
    protected $coursePublished                          = true;
    protected $courseMarketplace                        = false;
    protected $eventUnderstandWebIn4HoursId;
    protected $eventUnderstandWebIn4HoursTitle          = 'Understand WEB in 4 hours';
    protected $eventUnderstandWebIn4HoursAvailableSeats = 20;
    protected $eventWeb4EveryOneId;
    protected $eventWeb4EveryOneTitle                   = '[MEET-UP] Web for everyone';
    protected $eventWeb4EveryOneAvailableSeats          = -1; # yeah: FOR ALL, all can come!
    protected $module1Id;
    protected $moduleTitle                              = 'Basics of HTML';
    protected $moduleHtmlId;
    protected $moduleHtmlTitle                          = 'Basics of HTML';
    protected $moduleCssId;
    protected $moduleCssTitle                           = 'Introduction to CSS';

    protected function generatePortalData(Connection $go1, string $accountsName, bool $userData = true, bool $learningData = true)
    {
        $api = new class
        {
            use InstanceMockTrait;
            use UserMockTrait;
        };

        # Portal › Default
        # ---------------------
        $this->portalId = $api->createInstance($go1, [
            'title'      => $this->portalName,
            'status'     => $this->portalStatus,
            'is_primary' => $this->portalIsPrimary,
            'version'    => $this->portalVersion,
            'data'       => $this->portalData,
        ]);

        $api->createInstancePublicKey($go1, ['instance' => $this->portalName]);
        $api->createInstancePrivateKey($go1, ['instance' => $this->portalName]);
        $this->portalRoleAdminId = $api->createPortalAdminRole($go1, ['instance' => $this->portalName]);

        # Portal › Content provider
        # ---------------------
        $this->portalContentProviderId = $api->createInstance($go1, [
            'title'      => $this->portalContentProviderName,
            'status'     => $this->portalContentProviderStatus,
            'is_primary' => $this->portalContentProviderIsPrimary,
            'version'    => $this->portalContentProviderVersion,
            'data'       => $this->portalContentProviderData,
        ]);

        $api->createInstancePublicKey($go1, ['instance' => $this->portalContentProviderName]);
        $api->createInstancePrivateKey($go1, ['instance' => $this->portalContentProviderName]);
        $this->portalContentProviderRoleAdminId = $api->createPortalAdminRole($go1, ['instance' => $this->portalContentProviderName]);

        if ($userData) {
            $this->generateUserData($go1, $accountsName);

            if ($learningData) {
                $this->generateLearningData($go1);
            }
        }
    }

    protected function generateUserData(Connection $go1, string $accountsName)
    {
        $api = new class
        {
            use UserMockTrait;
        };

        # User › Portal admin
        # ---------------------
        $this->userAdminId = $api->createUser($go1, [
            'instance'   => $accountsName,
            'uuid'       => $this->userAdminUuid,
            'mail'       => $this->userAdminMail,
            'first_name' => $this->userAdminFirstName,
            'last_name'  => $this->userAdminLastName,
        ]);

        $this->userAdminAccountId = $api->createUser($go1, [
            'instance'   => $this->portalName,
            'mail'       => $this->userAdminMail,
            'first_name' => $this->userAdminFirstName,
            'last_name'  => $this->userAdminLastName,
        ]);

        $api->link($go1, EdgeTypes::HAS_ACCOUNT, $this->userAdminId, $this->userAdminAccountId);
        $api->link($go1, EdgeTypes::HAS_ROLE, $this->userAdminAccountId, $this->portalRoleAdminId);

        # User › Learner 1
        # ---------------------
        $api->link(
            $go1,
            EdgeTypes::HAS_ACCOUNT,
            $this->userLearner1Id = $api->createUser($go1, [
                'instance'   => $accountsName,
                'uuid'       => $this->userLearner1Uuid,
                'mail'       => $this->userLearner1Mail,
                'first_name' => $this->userLearner1FirstName,
                'last_name'  => $this->userLearner1LastName,
            ]),
            $this->userLearner1AccountId = $api->createUser($go1, [
                'instance'   => $this->portalName,
                'mail'       => $this->userLearner1Mail,
                'first_name' => $this->userLearner1FirstName,
                'last_name'  => $this->userLearner1LastName,
            ])
        );

        # User › Learner 2
        # ---------------------
        $api->link(
            $go1,
            EdgeTypes::HAS_ACCOUNT,
            $this->userLearner1Id = $api->createUser($go1, [
                'instance'   => $accountsName,
                'uuid'       => $this->userLearner2Uuid,
                'mail'       => $this->userLearner2Mail,
                'first_name' => $this->userLearner2FirstName,
                'last_name'  => $this->userLearner2LastName,
            ]),
            $this->userLearner1AccountId = $api->createUser($go1, [
                'instance'   => $this->portalName,
                'mail'       => $this->userLearner2Mail,
                'first_name' => $this->userLearner2FirstName,
                'last_name'  => $this->userLearner2LastName,
            ])
        );
    }

    protected function generateLearningData(Connection $go1)
    {
        $api = new class
        {
            use LoMockTrait;
            use UserMockTrait;
        };

        $this->courseWebId = $api->createCourse($go1, ['instance_id' => $this->portalId, 'title' => $this->courseWebId]);
        $this->moduleHtmlId = $api->createModule($go1, ['instance_id' => $this->portalId, 'title' => $this->moduleHtmlTitle]);
        $this->moduleCssId = $api->createModule($go1, ['instance_id' => $this->portalId, 'title' => $this->moduleCssTitle]);
        $this->eventUnderstandWebIn4HoursId = $api->createLO($go1, [
            'instance_id' => $this->portalId,
            'type'        => LiTypes::EVENT,
            'title'       => $this->eventUnderstandWebIn4HoursTitle,
        ]);

        $api->createEvent($go1, $this->eventUnderstandWebIn4HoursId, [
            'start' => '2018-01-26T08:19:00+0000',
            'seats' => $this->eventUnderstandWebIn4HoursAvailableSeats,
        ]);

        $this->eventWeb4EveryOneId = $api->createLO($go1, [
            'instance_id' => $this->portalId,
            'type'        => LiTypes::EVENT,
            'title'       => $this->eventWeb4EveryOneTitle,
        ]);

        $api->createEvent($go1, $this->eventWeb4EveryOneId, [
            'start' => '2018-01-26T08:19:00+0000',
            'seats' => $this->eventWeb4EveryOneAvailableSeats,
        ]);

        $api->link($go1, EdgeTypes::HAS_MODULE, $this->courseWebId, $this->moduleHtmlId);
        $api->link($go1, EdgeTypes::HAS_MODULE, $this->courseWebId, $this->moduleCssId);
        $api->link($go1, EdgeTypes::HAS_LI, $this->courseWebId, $this->eventWeb4EveryOneId);
        $api->link($go1, EdgeTypes::HAS_LI, $this->courseWebId, $this->eventUnderstandWebIn4HoursId);
    }
}
