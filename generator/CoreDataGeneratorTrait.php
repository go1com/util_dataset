<?php

namespace go1\util_dataset\generator;

use Doctrine\DBAL\Connection;
use go1\util_dataset\generator\core\LoDataGenerator;
use go1\util_dataset\generator\core\PortalDataGenerator;
use go1\util_dataset\generator\core\UserDataGenerator;
use ReflectionObject;
use ReflectionProperty;

/**
 * Core data generator.
 *
 * Portal › Default
 * ---------------------
 *
 * @property $portalId                                 int
 * @property $portalName                               string
 * @property $portalStatus                             boolean
 * @property $portalIsPrimary                          bool
 * @property $portalVersion                            string
 * @property $portalPublicKey                          string
 * @property $portalPrivateKey                         string
 * @property $portalData
 * @property $portalRoleAdminId                        int
 *
 * Portal › Content provider
 * ---------------------
 *
 * @property $portalContentProviderId                  int
 * @property $portalContentProviderName                string
 * @property $portalContentProviderStatus              bool
 * @property $portalContentProviderIsPrimary           bool
 * @property $portalContentProviderVersion             string
 * @property $portalContentProviderPublicKey           string
 * @property $portalContentProviderPrivateKey          string
 * @property $portalContentProviderData
 * @property $portalContentProviderRoleAdminId         int
 *
 *
 * # User › Portal admin
 * # ---------------------
 * @property $userAdminUuid                            string
 * @property $userAdminId                              int
 * @property $userAdminProfileId                       int
 * @property $userAdminAccountId                       int
 * @property $userAdminMail                            string
 * @property $userAdminFirstName                       string
 * @property $userAdminLastName                        string
 * @property $userAdminJwt                             string
 *
 * # User › Manager
 * # ---------------------
 * @property $userManagerUuid                          string
 * @property $userManagerId                            int
 * @property $userManagerAccountId                     int
 * @property $userManagerMail                          string
 * @property $userManagerFirstName                     string
 * @property $userManagerLastName                      string
 *
 * # User › Course author
 * # ---------------------
 * @property $userCourseAuthorUuid                     string
 * @property $userCourseAuthorId                       int
 * @property $userCourseAuthorAccountId                int
 * @property $userCourseAuthorMail                     string
 * @property $userCourseAuthorProfileId                int
 * @property $userCourseAuthorFirstName                string
 * @property $userCourseAuthorLastName                 string
 * @property $userCourseAuthorJwt                      string
 *
 * # User › Course assessor
 * # ---------------------
 * @property $userCourseAssessorUuid                   string
 * @property $userCourseAssessorId                     int
 * @property $userCourseAssessorAccountId              int
 * @property $userCourseAssessorMail                   string
 * @property $userCourseAssessorFirstName              string
 * @property $userCourseAssessorLastName               string
 *
 * # User › Enrolment assessor
 * # ---------------------
 * @property $userEnrolmentAssessorUuid                string
 * @property $userEnrolmentAssessorId                  int
 * @property $userEnrolmentAssessorAccountId           int
 * @property $userEnrolmentAssessorMail                string
 * @property $userEnrolmentAssessorFirstName           string
 * @property $userEnrolmentAssessorLastName            string
 *
 * # User › Learner 1
 * # ---------------------
 * @property $userLearner1Uuid                         string
 * @property $userLearner1Id                           int
 * @property $userLearner1ProfileId                    int
 * @property $userLearner1AccountId                    int
 * @property $userLearner1Mail                         string
 * @property $userLearner1FirstName                    string
 * @property $userLearner1LastName                     string
 * @property $userLearner1JWT                          string
 *
 * # User › Learner 2
 * # ---------------------
 * @property $userLearner2Uuid                         string
 * @property $userLearner2Id                           int
 * @property $userLearner2ProfileId                    int
 * @property $userLearner2AccountId                    int
 * @property $userLearner2Mail                         string
 * @property $userLearner2FirstName                    string
 * @property $userLearner2LastName                     string
 * @property $userLearner2JWT                          string
 *
 * @property $courseWebId
 * @property $courseWebTitle                           string
 * @property $courseWebPublished                       bool
 * @property $courseWebMarketplace                     bool
 * @property $courseWebAuthorMail                      string
 * @property $eventUnderstandWebIn4HoursLiId
 * @property $eventUnderstandWebIn4HoursId
 * @property $eventUnderstandWebIn4HoursTitle          = 'Understand WEB in 4 hours'
 * @property $eventUnderstandWebIn4HoursAvailableSeats = 20
 * @property $eventWeb4EveryOneLiId
 * @property $eventWeb4EveryOneId
 * @property $eventWeb4EveryOneTitle                   = '[MEET-UP] Web for everyone'
 * @property $eventWeb4EveryOneAvailableSeats          = -1
 * @property $module1Id
 * @property $moduleTitle                              = 'Basics of HTML'
 * @property $moduleHtmlId
 * @property $moduleHtmlTitle                          = 'Basics of HTML'
 * @property $moduleCssId
 * @property $moduleCssTitle                           = 'Introduction to CSS'
 */
trait CoreDataGeneratorTrait
{
    /** @var Connection */
    public $go1;

    /** @var string */
    public $accountsName;

    public static function cloneProperties(&$from, &$to)
    {
        $rTrait = new ReflectionObject($from);
        foreach ($rTrait->getProperties(ReflectionProperty::IS_PUBLIC) as $rProperty) {
            if (!property_exists($to, $rProperty->getName())) {
                $to->{$rProperty->getName()} = $rProperty->getValue($from);
            }
        }
    }

    protected function generatePortalData(
        Connection $go1,
        string $accountsName,
        bool $userData = true,
        bool $learningData = true)
    {
        $this->go1 = $go1;
        $this->accountsName = $accountsName;

        (new PortalDataGenerator)
            ->generate(
                $this,
                !$userData ? null : function () use ($learningData) {
                    (new UserDataGenerator)
                        ->generate(
                            $this,
                            !$learningData ? null : function () {
                                (new LoDataGenerator)->generate($this);
                            }
                        );
                }
            );
    }
}
