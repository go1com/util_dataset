<?php

namespace go1\util_dataset\generator\core;

use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util_dataset\generator\CoreDataGeneratorTrait;

class EnrolmentDataGenerator implements DataGeneratorInterface
{
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
            use EnrolmentMockTrait;
        };

        $trait->learner1CourseWebEnrolmentId = $api->createEnrolment($trait->go1, [
            'taken_instance_id' => $trait->portalId,
            'lo_id'             => $trait->courseWebId,
            'profile_id'        => $trait->userLearner1ProfileId,
        ]);
        $trait->learner1CourseWebRevisionEnrolmentId = $api->createRevisionEnrolment($trait->go1, [
            'enrolment_id'      => $trait->learner1CourseWebEnrolmentId,
            'profile_id'        => $trait->userLearner1ProfileId,
            'lo_id'             => $trait->courseWebId,
            'taken_instance_id' => $trait->portalId,
        ]);
        $trait->learner1ModuleCssEnrolmentId = $api->createEnrolment($trait->go1, [
            'taken_instance_id'   => $trait->portalId,
            'lo_id'               => $trait->moduleCssId,
            'parent_lo_id'        => $trait->courseWebId,
            'profile_id'          => $trait->userLearner1ProfileId,
            'parent_enrolment_id' => $trait->learner1CourseWebEnrolmentId,
        ]);
        $trait->learner1ModuleCssRevisionEnrolmentId = $api->createRevisionEnrolment($trait->go1, [
            'enrolment_id'      => $trait->learner1ModuleCssEnrolmentId,
            'profile_id'        => $trait->userLearner1ProfileId,
            'lo_id'             => $trait->moduleCssId,
            'taken_instance_id' => $trait->portalId,
        ]);
    }
}
