<?php

namespace go1\util_dataset\generator\core;

use go1\util\edge\EdgeTypes;
use go1\util\lo\LiTypes;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\UserMockTrait;
use go1\util_dataset\generator\CoreDataGeneratorTrait;

class LoDataGenerator
{
    # Course: Making web 101
    #   Event:
    #       1. Understand the web in 4 hours.
    #       2. Web for everyone meet-up.
    #   Modules:
    #       1. Basics of HTML
    #       2. Introduction to CSS
    # ---------------------
    public $courseWebId;
    public $courseWebTitle                           = 'Making web 101';
    public $courseWebPublished                       = true;
    public $courseWebMarketplace                     = false;
    public $courseWebAuthorMail                      = 'tham.vu@qa.com';
    public $eventUnderstandWebIn4HoursLiId;
    public $eventUnderstandWebIn4HoursId;
    public $eventUnderstandWebIn4HoursTitle          = 'Understand WEB in 4 hours';
    public $eventUnderstandWebIn4HoursAvailableSeats = 20;
    public $eventWeb4EveryOneLiId;
    public $eventWeb4EveryOneId;
    public $eventWeb4EveryOneTitle                   = '[MEET-UP] Web for everyone';
    public $eventWeb4EveryOneAvailableSeats          = -1; # yeah: FOR ALL, all can come!
    public $module1Id;
    public $moduleTitle                              = 'Basics of HTML';
    public $moduleHtmlId;
    public $moduleHtmlTitle                          = 'Basics of HTML';
    public $moduleCssId;
    public $moduleCssTitle                           = 'Introduction to CSS';

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
            use LoMockTrait;
            use UserMockTrait;
        };

        $trait->courseWebId = $api->createCourse($trait->go1, ['instance_id' => $trait->portalId, 'title' => $trait->courseWebId, 'published' => $trait->courseWebPublished, 'marketplace' => $trait->courseWebMarketplace]);

        if ($trait->courseWebAuthorMail) {
            $authorUserId = $trait->go1->fetchColumn('SELECT id FROM gc_user WHERE instance = ? AND mail = ?', [$trait->accountsName, $trait->courseWebAuthorMail]);
            if ($authorUserId) {
                $api->link($trait->go1, EdgeTypes::HAS_AUTHOR_EDGE, $trait->courseWebId, $authorUserId);
            }
        }

        $trait->moduleHtmlId = $api->createModule($trait->go1, ['instance_id' => $trait->portalId, 'title' => $trait->moduleHtmlTitle]);
        $trait->moduleCssId = $api->createModule($trait->go1, ['instance_id' => $trait->portalId, 'title' => $trait->moduleCssTitle]);
        $trait->eventUnderstandWebIn4HoursLiId = $api->createLO($trait->go1, [
            'instance_id' => $trait->portalId,
            'type'        => LiTypes::EVENT,
            'title'       => $trait->eventUnderstandWebIn4HoursTitle,
        ]);

        $trait->eventUnderstandWebIn4HoursId = $api->createEvent($trait->go1, $trait->eventUnderstandWebIn4HoursLiId, [
            'start' => '2018-01-26T08:19:00+0000',
            'seats' => $trait->eventUnderstandWebIn4HoursAvailableSeats,
        ]);

        $trait->eventWeb4EveryOneLiId = $api->createLO($trait->go1, [
            'instance_id' => $trait->portalId,
            'type'        => LiTypes::EVENT,
            'title'       => $trait->eventWeb4EveryOneTitle,
        ]);

        $trait->eventWeb4EveryOneId = $api->createEvent($trait->go1, $trait->eventWeb4EveryOneLiId, [
            'start' => '2018-01-26T08:19:00+0000',
            'seats' => $trait->eventWeb4EveryOneAvailableSeats,
        ]);

        $api->link($trait->go1, EdgeTypes::HAS_MODULE, $trait->courseWebId, $trait->moduleHtmlId);
        $api->link($trait->go1, EdgeTypes::HAS_MODULE, $trait->courseWebId, $trait->moduleCssId);
        $api->link($trait->go1, EdgeTypes::HAS_LI, $trait->courseWebId, $trait->eventWeb4EveryOneLiId);
        $api->link($trait->go1, EdgeTypes::HAS_LI, $trait->courseWebId, $trait->eventUnderstandWebIn4HoursLiId);
    }
}
