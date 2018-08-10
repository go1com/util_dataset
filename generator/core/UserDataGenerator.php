<?php

namespace go1\util_dataset\generator\core;

use go1\util\edge\EdgeTypes;
use go1\util\schema\mock\UserMockTrait;
use go1\util_dataset\generator\CoreDataGeneratorTrait;

class UserDataGenerator
{
    # User › Portal admin
    # ---------------------
    public $userAdminUuid      = '1a24235e-30f6-49cb-9e5c-3aaeff046288';
    public $userAdminId;
    public $userAdminProfileId = 119911;
    public $userAdminAccountId;
    public $userAdminMail      = 'dang.phan@qa.com';
    public $userAdminFirstName = 'Dang';
    public $userAdminLastName  = 'Phan';
    public $userAdminJwt;

    # User › Manager
    # ---------------------
    public $userManagerUuid      = 'fbe840af-123a-4136-9c79-2bdd86c19748';
    public $userManagerId;
    public $userManagerAccountId;
    public $userManagerMail      = 'kien.nguyen@qa.com';
    public $userManagerFirstName = 'Kien';
    public $userManagerLastName  = 'Nguyen';

    # User › Course author
    # ---------------------
    public $userCourseAuthorUuid      = '6cf00234-918e-46f1-b4e2-11aa0467d31d';
    public $userCourseAuthorId;
    public $userCourseAuthorAccountId;
    public $userCourseAuthorMail      = 'tham.vu@qa.com';
    public $userCourseAuthorProfileId = 99125;
    public $userCourseAuthorFirstName = 'Tham';
    public $userCourseAuthorLastName  = 'Vu';
    public $userCourseAuthorJwt;

    # User › Course assessor
    # ---------------------
    public $userCourseAssessorUuid      = '234f2a74-a308-47bd-a6d4-d1488d840233';
    public $userCourseAssessorId;
    public $userCourseAssessorAccountId;
    public $userCourseAssessorMail      = 'si.nguyen@qa.com';
    public $userCourseAssessorFirstName = 'Si';
    public $userCourseAssessorLastName  = 'Nguyen';

    # User › Enrolment assessor
    # ---------------------
    public $userEnrolmentAssessorUuid      = '903d044e-8945-4115-ae36-526452501c15';
    public $userEnrolmentAssessorId;
    public $userEnrolmentAssessorAccountId;
    public $userEnrolmentAssessorMail      = 'thu.le@qa.com';
    public $userEnrolmentAssessorFirstName = 'Thu';
    public $userEnrolmentAssessorLastName  = 'Le';

    # User › Learner 1
    # ---------------------
    public $userLearner1Uuid      = '51eb3cc1-b65c-4278-94fa-62005e5df5d7';
    public $userLearner1Id;
    public $userLearner1ProfileId = 6699;
    public $userLearner1AccountId;
    public $userLearner1Mail      = 'chau.pham@qa.com';
    public $userLearner1FirstName = 'Chau';
    public $userLearner1LastName  = 'Pham';
    public $userLearner1JWT;

    # User › Learner 2
    # ---------------------
    public $userLearner2Uuid      = 'bf650d4e-e33d-4cbb-be10-df6334e8120c';
    public $userLearner2Id;
    public $userLearner2ProfileId = 6969;
    public $userLearner2AccountId;
    public $userLearner2Mail      = 'quan.vo@qa.com';
    public $userLearner2FirstName = 'Quan';
    public $userLearner2LastName  = 'Vo';
    public $userLearner2JWT;

    # Content provider portal › admin
    # ---------------------
    public $cpUserAdminUuid      = 'c4ced60c-a6b9-4249-b956-b25ea144e4c9';
    public $cpUserAdminId;
    public $cpUserAdminProfileId = 112211;
    public $cpUserAdminAccountId;
    public $cpUserAdminMail      = 'quang.t@qa.com';
    public $cpUserAdminFirstName = 'Quang';
    public $cpUserAdminLastName  = 'To';
    public $cpUserAdminJwt;

    # Content provider portal › user
    # ---------------------
    public $cpUserLearner1Uuid      = '78112a7d-b47d-4c9a-9353-2993dcf25c9c';
    public $cpUserLearner1Id;
    public $cpUserLearner1ProfileId = 7799;
    public $cpUserLearner1AccountId;
    public $cpUserLearner1Mail      = 'vi.mac@qa.com';
    public $cpUserLearner1FirstName = 'Vi';
    public $cpUserLearner1LastName  = 'Mac';
    public $cpUserLearner1JWT;

    /**
     * @param CoreDataGeneratorTrait $trait
     * @param callable|null          $callback
     */
    public function generate(&$trait, callable $callback = null)
    {
        $trait::cloneProperties($this, $trait);
        $this->doGenerate($trait);
        $this->doGenerateUserForContentProviderPortal($trait);
        $callback && call_user_func($callback);
    }

    /**
     * @param CoreDataGeneratorTrait $trait
     */
    private function doGenerate($trait)
    {
        $api = new class
        {
            use UserMockTrait;
        };

        # User › Portal admin
        # ---------------------
        $trait->userAdminId = $api->createUser($trait->go1, [
            'instance'   => $trait->accountsName,
            'uuid'       => $trait->userAdminUuid,
            'profile_id' => $trait->userAdminProfileId,
            'mail'       => $trait->userAdminMail,
            'first_name' => $trait->userAdminFirstName,
            'last_name'  => $trait->userAdminLastName,
        ]);

        $trait->userAdminAccountId = $api->createUser($trait->go1, [
            'instance'   => $trait->portalName,
            'mail'       => $trait->userAdminMail,
            'first_name' => $trait->userAdminFirstName,
            'last_name'  => $trait->userAdminLastName,
        ]);

        $api->link($trait->go1, EdgeTypes::HAS_ACCOUNT, $trait->userAdminId, $trait->userAdminAccountId);
        $api->link($trait->go1, EdgeTypes::HAS_ROLE, $trait->userAdminAccountId, $trait->portalRoleAdminId);
        $trait->userAdminJwt = $api->jwtForUser($trait->go1, $trait->userAdminId, $trait->portalName);

        # User › Course author
        # ---------------------
        $api->link(
            $trait->go1,
            EdgeTypes::HAS_ACCOUNT,
            $trait->userCourseAuthorId = $api->createUser($trait->go1, [
                'instance'   => $trait->accountsName,
                'uuid'       => $trait->userCourseAuthorUuid,
                'mail'       => $trait->userCourseAuthorMail,
                'profile_id' => $trait->userCourseAuthorProfileId,
                'first_name' => $trait->userCourseAuthorFirstName,
                'last_name'  => $trait->userCourseAuthorLastName,
            ]),
            $trait->userCourseAuthorAccountId = $api->createUser($trait->go1, [
                'instance'   => $trait->portalName,
                'mail'       => $trait->userCourseAuthorMail,
                'first_name' => $trait->userCourseAuthorFirstName,
                'last_name'  => $trait->userCourseAuthorLastName,
            ])
        );

        $trait->userCourseAuthorJwt = $api->jwtForUser($trait->go1, $trait->userCourseAuthorId, $trait->portalName);

        # User › Learner 1
        # ---------------------
        $api->link(
            $trait->go1,
            EdgeTypes::HAS_ACCOUNT,
            $trait->userLearner1Id = $api->createUser($trait->go1, [
                'instance'   => $trait->accountsName,
                'uuid'       => $trait->userLearner1Uuid,
                'mail'       => $trait->userLearner1Mail,
                'profile_id' => $trait->userLearner1ProfileId,
                'first_name' => $trait->userLearner1FirstName,
                'last_name'  => $trait->userLearner1LastName,
            ]),
            $trait->userLearner1AccountId = $api->createUser($trait->go1, [
                'instance'   => $trait->portalName,
                'mail'       => $trait->userLearner1Mail,
                'profile_id' => $trait->userLearner1ProfileId + 10000,
                'first_name' => $trait->userLearner1FirstName,
                'last_name'  => $trait->userLearner1LastName,
            ])
        );
        $trait->userLearner1JWT = $api->jwtForUser($trait->go1, $trait->userLearner1Id, $trait->portalName);

        # User › Learner 2
        # ---------------------
        $api->link(
            $trait->go1,
            EdgeTypes::HAS_ACCOUNT,
            $trait->userLearner2Id = $api->createUser($trait->go1, [
                'instance'   => $trait->accountsName,
                'uuid'       => $trait->userLearner2Uuid,
                'mail'       => $trait->userLearner2Mail,
                'profile_id' => $trait->userLearner2ProfileId + 10000,
                'first_name' => $trait->userLearner2FirstName,
                'last_name'  => $trait->userLearner2LastName,
            ]),
            $trait->userLearner2AccountId = $api->createUser($trait->go1, [
                'instance'   => $trait->portalName,
                'mail'       => $trait->userLearner2Mail,
                'profile_id' => $trait->userLearner2ProfileId + 10000,
                'first_name' => $trait->userLearner2FirstName,
                'last_name'  => $trait->userLearner2LastName,
            ])
        );

        $trait->userLearner2JWT = $api->jwtForUser($trait->go1, $trait->userLearner2Id, $trait->portalName);
    }

    /**
     * @param CoreDataGeneratorTrait $trait
     */
    private function doGenerateUserForContentProviderPortal($trait)
    {
        $api = new class
        {
            use UserMockTrait;
        };

        # User › Portal admin
        # ---------------------
        $trait->cpUserAdminId = $api->createUser($trait->go1, [
            'instance'   => $trait->accountsName,
            'uuid'       => $trait->cpUserAdminUuid,
            'profile_id' => $trait->cpUserAdminProfileId,
            'mail'       => $trait->cpUserAdminMail,
            'first_name' => $trait->cpUserAdminFirstName,
            'last_name'  => $trait->cpUserAdminLastName,
        ]);

        $trait->cpUserAdminAccountId = $api->createUser($trait->go1, [
            'instance'   => $trait->portalContentProviderName,
            'mail'       => $trait->cpUserAdminMail,
            'profile_id' => $trait->cpUserAdminProfileId + 10000,
            'first_name' => $trait->cpUserAdminFirstName,
            'last_name'  => $trait->cpUserAdminLastName,
        ]);

        $api->link($trait->go1, EdgeTypes::HAS_ACCOUNT, $trait->cpUserAdminId, $trait->cpUserAdminAccountId);
        $api->link($trait->go1, EdgeTypes::HAS_ROLE, $trait->cpUserAdminAccountId, $trait->portalContentProviderRoleAdminId);
        $trait->cpUserAdminJwt = $api->jwtForUser($trait->go1, $trait->cpUserAdminId, $trait->portalContentProviderName);

        # User > Learner
        # ---------------------
        $api->link(
            $trait->go1,
            EdgeTypes::HAS_ACCOUNT,
            $trait->cpUserLearner1Id = $api->createUser($trait->go1, [
                'instance'   => $trait->accountsName,
                'uuid'       => $trait->cpUserLearner1Uuid,
                'mail'       => $trait->cpUserLearner1Mail,
                'profile_id' => $trait->cpUserLearner1ProfileId,
                'first_name' => $trait->cpUserLearner1FirstName,
                'last_name'  => $trait->cpUserLearner1LastName,
            ]),
            $trait->cpUserLearner1AccountId = $api->createUser($trait->go1, [
                'instance'   => $trait->portalContentProviderName,
                'mail'       => $trait->cpUserLearner1Mail,
                'profile_id' => $trait->cpUserLearner1ProfileId + 10000,
                'first_name' => $trait->cpUserLearner1FirstName,
                'last_name'  => $trait->cpUserLearner1LastName,
            ])
        );
        $trait->cpUserLearner1JWT = $api->jwtForUser($trait->go1, $trait->cpUserLearner1Id, $trait->portalContentProviderName);
    }
}
