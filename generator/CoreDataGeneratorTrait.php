<?php

namespace go1\util_dataset\generator;

use Doctrine\DBAL\Connection;
use Exception;
use go1\util_dataset\generator\core\DataGeneratorInterface;
use go1\util_dataset\generator\core\EnrolmentDataGenerator;
use go1\util_dataset\generator\core\LoDataGenerator;
use go1\util_dataset\generator\core\MarketplaceDataGenerator;
use go1\util_dataset\generator\core\PortalDataGenerator;
use go1\util_dataset\generator\core\UserDataGenerator;
use ReflectionObject;
use ReflectionProperty;

/**
 * Core data generator.
 *
 *
 * Example usage:
 * ---------------------
 *
 * class MyTest extends \PHPUnit\Framework\TestCase {
 * use \go1\util_dataset\generator\CoreDataGeneratorTrait;
 *
 *  public function testSomeFeature() {
 *      $this->portalName = 'WeChangeOverride.portal.name';
 *      $app = $this->getApp();
 *      $this->generatePortalData($app['dbs']['go1'], $app['accounts_name']);
 *  }
 * }
 *
 * Portal › Default
 * ---------------------
 *
 * @property     $portalId                                 int
 * @property     $portalName                               string
 * @property     $portalStatus                             boolean
 * @property     $portalIsPrimary                          bool
 * @property     $portalVersion                            string
 * @property     $portalPublicKey                          string
 * @property     $portalPrivateKey                         string
 * @property     $portalData
 * @property     $portalRoleAdminId                        int
 * @property     $portalRoleManagerId                      int
 * @property     $portalGroupId                            int
 *
 * Portal › Content provider
 * ---------------------
 *
 * @property     $portalContentProviderId                  int
 * @property     $portalContentProviderName                string
 * @property     $portalContentProviderStatus              bool
 * @property     $portalContentProviderIsPrimary           bool
 * @property     $portalContentProviderVersion             string
 * @property     $portalContentProviderPublicKey           string
 * @property     $portalContentProviderPrivateKey          string
 * @property     $portalContentProviderData
 * @property     $portalContentProviderRoleAdminId         int
 * @property     $portalContentProviderRoleManagerId       int
 *
 *
 * # User › Portal admin
 * # ---------------------
 * @property     $userAdminUuid                            string
 * @property     $userAdminId                              int
 * @property     $userAdminProfileId                       int
 * @property     $userAdminAccountId                       int
 * @property     $userAdminMail                            string
 * @property     $userAdminFirstName                       string
 * @property     $userAdminLastName                        string
 * @property     $userAdminJwt                             string
 *
 * # User › Manager
 * # ---------------------
 * @property     $userManagerUuid                          string
 * @property     $userManagerId                            int
 * @property     $userManagerProfileId                     int
 * @property     $userManagerAccountId                     int
 * @property     $userManagerMail                          string
 * @property     $userManagerFirstName                     string
 * @property     $userManagerLastName                      string
 * @property     $userManagerJwt                           string
 *
 * # User › Course author
 * # ---------------------
 * @property     $userCourseAuthorUuid                     string
 * @property     $userCourseAuthorId                       int
 * @property     $userCourseAuthorAccountId                int
 * @property     $userCourseAuthorMail                     string
 * @property     $userCourseAuthorProfileId                int
 * @property     $userCourseAuthorFirstName                string
 * @property     $userCourseAuthorLastName                 string
 * @property     $userCourseAuthorJwt                      string
 *
 * # User › Course assessor
 * # ---------------------
 * @property     $userCourseAssessorUuid                   string
 * @property     $userCourseAssessorId                     int
 * @property     $userCourseAssessorAccountId              int
 * @property     $userCourseAssessorMail                   string
 * @property     $userCourseAssessorFirstName              string
 * @property     $userCourseAssessorLastName               string
 *
 * # User › Enrolment assessor
 * # ---------------------
 * @property     $userEnrolmentAssessorUuid                string
 * @property     $userEnrolmentAssessorId                  int
 * @property     $userEnrolmentAssessorAccountId           int
 * @property     $userEnrolmentAssessorMail                string
 * @property     $userEnrolmentAssessorFirstName           string
 * @property     $userEnrolmentAssessorLastName            string
 *
 * # User › Learner 1
 * # ---------------------
 * @property     $userLearner1Uuid                         string
 * @property     $                          int
 * @property     $userLearner1ProfileId                    int
 * @property     $userLearner1AccountId                    int
 * @property     $userLearner1Mail                         string
 * @property     $userLearner1FirstName                    string
 * @property     $userLearner1LastName                     string
 * @property     $userLearner1JWT                          string
 *
 * # User › Learner 2
 * # ---------------------
 * @property     $userLearner2Uuid                         string
 * @property     $userLearner2Id                           int
 * @property     $userLearner2ProfileId                    int
 * @property     $userLearner2AccountId                    int
 * @property     $userLearner2Mail                         string
 * @property     $userLearner2FirstName                    string
 * @property     $userLearner2LastName                     string
 * @property     $userLearner2JWT                          string
 *
 * # Content provider portal › admin
 * # ---------------------
 * @property     $cpUserAdminUuid                          string
 * @property     $cpUserAdminId                            int
 * @property     $cpUserAdminProfileId                     int
 * @property     $cpUserAdminAccountId                     int
 * @property     $cpUserAdminMail                          string
 * @property     $cpUserAdminFirstName                     string
 * @property     $cpUserAdminLastName                      string
 * @property     $cpUserAdminJwt                           string
 *
 * # Content provider portal › user
 * # ---------------------
 * @property     $cpUserLearner1Uuid                       string
 * @property     $cpUserLearner1Id                         int
 * @property     $cpUserLearner1ProfileId                  int
 * @property     $cpUserLearner1AccountId                  int
 * @property     $cpUserLearner1Mail                       string
 * @property     $cpUserLearner1FirstName                  string
 * @property     $cpUserLearner1LastName                   string
 * @property     $cpUserLearner1JWT                        string
 *
 * # Learning objects.
 * # ---------------------
 * @property     $courseWebId
 * @property     $courseWebTitle                           string
 * @property     $courseWebPublished                       bool
 * @property     $courseWebMarketplace                     bool
 * @property     $courseWebAuthorMail                      string
 * @property     $eventUnderstandWebIn4HoursLiId           int
 * @property     $eventUnderstandWebIn4HoursId             int
 * @property     $eventUnderstandWebIn4HoursTitle          string
 * @property     $eventUnderstandWebIn4HoursAvailableSeats int
 * @property     $eventWeb4EveryOneLiId                    int
 * @property     $eventWeb4EveryOneId                      int
 * @property     $eventWeb4EveryOneTitle                   string
 * @property     $eventWeb4EveryOneAvailableSeats          int
 * @property     $module1Id                                int
 * @property     $moduleTitle                              string
 * @property     $moduleHtmlId                             int
 * @property     $moduleHtmlTitle                          string
 * @property     $moduleCssId                              id
 * @property     $moduleCssTitle                           string
 * @property     $liIntroductionToTagsId                   int
 * @property     $liIntroductionToElementsId               int
 * @property     $liIntroductionToAttributesId             int
 * @property     $liSelectorsAndVisualRulesId              int
 * @property     $liTheBoxModelId                          int
 * @property     $liDisplayAndPositioningId                int
 * @property     $liColorsId                               int
 * @property     $liTypographyId                           int
 * @property     $liGridId                                 int
 * @property     $liIntroductionToTagsTitle                string
 * @property     $liIntroductionToElementsTitle            string
 * @property     $liIntroductionToAttributesTitle          string
 * @property     $liSelectorsAndVisualRulesTitle           string
 * @property     $liTheBoxModelTitle                       string
 * @property     $liDisplayAndPositioningTitle             string
 * @property     $liColorsTitle                            string
 * @property     $liTypographyTitle                        string
 * @property     $liGridTitle                              string
 *
 * # Marketplace content
 * # ---------------------
 * @property     $marketplaceCourseId                      int
 * @property     $marketplaceCourseTitle                   string
 * @property     $marketplaceCourseStatus                  bool
 * @property     $marketplaceCourseMarketplace             bool
 * @property     $marketplaceCourseShareWithPortals        string[]
 *
 * # Enrolment
 * # ---------------------
 * @property int $learner1CourseWebEnrolmentId
 * @property int $learner1CourseWebRevisionEnrolmentId
 * @property int $learner1ModuleCssEnrolmentId
 * @property int $learner1ModuleCssRevisionEnrolmentId
 */
trait CoreDataGeneratorTrait
{
    /** @var Connection */
    public $go1;
    public $social;

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
        Connection $social,
        string $accountsName,
        array $flags = [])
    {
        $this->go1 = $go1;
        $this->social = $social;
        $this->accountsName = $accountsName;
        $this->generate([
            [PortalDataGenerator::class, $flags['portal'] ?? true],
            [UserDataGenerator::class, $flags['user'] ?? true],
            [LoDataGenerator::class, $flags['lo'] ?? true],
            [MarketplaceDataGenerator::class, $flags['marketplace'] ?? true],
            [EnrolmentDataGenerator::class, $flags['enrolment'] ?? true],
        ]);
    }

    private function generate(array $generators, int $index = 0)
    {
        if (!isset($generators[$index])) {
            return;
        }

        $generator = new $generators[$index][0];
        if (!$generator instanceof DataGeneratorInterface) {
            $msg = sprintf('Generator %s should implement interface %s.', $generators[$index], DataGeneratorInterface::class);
            throw new Exception($msg);
        }

        $generator->generate(
            $this,
            empty($generators[$index][1]) ? null : function () use (&$generators, $index) {
                $this->generate($generators, $index + 1);
            }
        );
    }
}
