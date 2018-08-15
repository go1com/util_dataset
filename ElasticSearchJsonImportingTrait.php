<?php

namespace go1\util_dataset;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Elasticsearch\Client;
use go1\util\edge\EdgeTypes;
use go1\util\enrolment\EnrolmentStatuses;
use go1\util\es\Schema;
use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util\schema\mock\LoMockTrait;
use go1\util\schema\mock\PortalMockTrait;
use go1\util\schema\mock\UserMockTrait;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Search;
use stdClass;

trait ElasticSearchJsonImportingTrait
{
    protected function importMapping(string $jsonDirectory)
    {
        return [
            # Order is important
            Schema::O_ENROLMENT => $jsonDirectory . '/portal.json',
            Schema::O_ENROLMENT => $jsonDirectory . '/enrolments.json',
            Schema::O_ACCOUNT   => $jsonDirectory . '/accounts.json',
            Schema::O_LO        => $jsonDirectory . '/learning-objects.json',
        ];
    }

    protected function import(Connection $go1, Client $es, string $portalIndex, string $accountsName, string $jsonDirectory)
    {
        foreach (static::importMapping($jsonDirectory) as $type => $file) {
            if (!file_exists($file)) {
                continue;
            }

            $objects = json_decode(file_get_contents($file));

            foreach ($objects as $object) {
                $es->index([
                    'index'   => (Schema::O_PORTAL == $type) ? Schema::INDEX : $portalIndex,
                    'routing' => $portalIndex,
                    'type'    => $type,
                    'id'      => $object->id,
                    'body'    => $object,
                    'refresh' => true,
                ]);

                switch ($type) {
                    case Schema::O_PORTAL:
                        $this->importPortal($go1, $es, $portalIndex, $object);
                        break;

                    case Schema::O_ENROLMENT:
                        $this->importEnrolment($go1, $es, $portalIndex, $object);
                        break;

                    case Schema::O_ACCOUNT:
                        $this->importAccount($go1, $es, $portalIndex, $object, $accountsName);
                        break;

                    case Schema::O_LO:
                        $this->importLearningObject($go1, $es, $portalIndex, $object);

                        break;
                }
            }
        }
    }

    protected function importPortal(Connection $go1, Client $es, $portalIndex, $object)
    {
        $api = new class
        {
            use PortalMockTrait;

            public function createPortal(Connection $db, array $options)
            {
                return $this->createPortal($db, $options);
            }
        };

        $api->createPortal($go1, [
            'id'        => $object->id,
            'title'     => $object->title,
            'status'    => $object->status,
            'version'   => $object->version,
            'data'      => [],
            'timestamp' => time(),
            'created'   => time(),
        ]);
    }

    protected function importEnrolment(Connection $go1, Client $es, string $portalIndex, $object)
    {
        $api = new class
        {
            use EnrolmentMockTrait;
            use UserMockTrait;
        };

        $go1->update('gc_user', ['profile_id' => $object->profile_id], ['id' => $object->metadata->user_id]);

        $enrolmentId = $api->createEnrolment($go1, [
            'id'                => $object->id,
            'lo_id'             => $object->lo->id,
            'profile_id'        => $object->profile_id,
            'status'            => EnrolmentStatuses::toString($object->status),
            'taken_instance_id' => $object->metadata->instance_id,
        ]);

        if (!empty($object->assessors)) {
            foreach ($object->assessors as $assessorId) {
                $api->link(
                    $go1,
                    EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE,
                    $assessorId,
                    $enrolmentId
                );
            }
        }
    }

    protected function importAccount(Connection $go1, Client $es, string $portalIndex, stdClass $object, string $accountsName)
    {
        $api = new class
        {
            use EnrolmentMockTrait;
            use UserMockTrait;
        };

        $findUserId = function ($mail) use ($es, $portalIndex) {
            $results = $es->search([
                'index'           => $portalIndex,
                'type'            => Schema::O_ENROLMENT,
                '_source_include' => ['metadata.user_id'],
                'size'            => 1,
                'body'            => $body = (new Search)
                    ->addQuery(new TermQuery('account.mail', $mail))
                    ->toArray(),
            ]);

            return $results['hits']['hits'][0]['_source']['metadata']['user_id'] ?? null;
        };

        try {
            $api->link(
                $go1,
                EdgeTypes::HAS_ACCOUNT,
                $api->createUser($go1, [
                    'id'         => $findUserId($object->mail),
                    'instance'   => $accountsName,
                    'mail'       => $object->mail,
                    'first_name' => $object->first_name,
                    'last_name'  => $object->last_name,
                ]),
                $api->createUser($go1, [
                    'id'         => $object->id,
                    'instance'   => $object->instance,
                    'mail'       => $object->mail,
                    'first_name' => $object->first_name,
                    'last_name'  => $object->last_name,
                    'profile_id' => $object->profile_id,
                ])
            );
        } catch (UniqueConstraintViolationException $e) {
        }
    }

    protected function importLearningObject(Connection $go1, Client $es, string $portalIndex, $object)
    {
        $api = new class
        {
            use EnrolmentMockTrait;
            use LoMockTrait;
            use UserMockTrait;
        };

        $loId = $api->createLO($go1, [
            'id'          => $object->id,
            'title'       => $object->title,
            'type'        => $object->type,
            'instance_id' => $object->instance_id,
        ]);

        if (!empty($object->authors)) {
            foreach ($object->authors as $author) {
                $api->link($go1, EdgeTypes::HAS_AUTHOR_EDGE, $loId, $author->id);
            }
        }
    }
}
