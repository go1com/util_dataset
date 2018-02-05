<?php

namespace go1\util_dataset;

use Doctrine\DBAL\Connection;
use Elasticsearch\Client;
use go1\util\edge\EdgeTypes;
use go1\util\enrolment\EnrolmentStatuses;
use go1\util\es\Schema;
use go1\util\schema\mock\EnrolmentMockTrait;
use go1\util\schema\mock\UserMockTrait;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Search;

trait ElasticSearchJsonImporting
{
    protected function import(Client $es, $portalIndex, string $jsonDirectory)
    {
        $_ = [
            Schema::O_ENROLMENT => $jsonDirectory . '/enrolments.json',
            Schema::O_ACCOUNT   => $jsonDirectory . '/accounts.json',
            Schema::O_LO        => $jsonDirectory . '/learning-objects.json',
        ];

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

        $mock = new class
        {
            use EnrolmentMockTrait;
            use UserMockTrait;
        };

        foreach ($_ as $type => $file) {
            $objects = json_decode(file_get_contents($file));

            foreach ($objects as $object) {
                $es->index([
                    'index'   => $portalIndex,
                    'routing' => $portalIndex,
                    'type'    => $type,
                    'id'      => $object->id,
                    'body'    => $object,
                    'refresh' => true,
                ]);

                switch ($type) {
                    case Schema::O_ACCOUNT:
                        try {
                            $this->link(
                                $go1,
                                EdgeTypes::HAS_ACCOUNT,
                                $this->createUser($go1, [
                                    'id'         => $findUserId($object->mail),
                                    'instance'   => $accountsName,
                                    'mail'       => $object->mail,
                                    'first_name' => $object->first_name,
                                    'last_name'  => $object->last_name,
                                ]),
                                $this->createUser($go1, [
                                    'id'         => $object->id,
                                    'instance'   => $object->instance,
                                    'mail'       => $object->mail,
                                    'first_name' => $object->first_name,
                                    'last_name'  => $object->last_name,
                                    'profile_id' => $object->profile_id,
                                ])
                            );
                        }
                        catch (UniqueConstraintViolationException $e) {
                        }
                        break;

                    case Schema::O_LO:
                        $loId = $this->createLO($go1, [
                            'id'          => $object->id,
                            'title'       => $object->title,
                            'type'        => $object->type,
                            'instance_id' => $object->instance_id,
                        ]);

                        if (!empty($object->authors)) {
                            foreach ($object->authors as $author) {
                                $this->link($go1, EdgeTypes::HAS_AUTHOR_EDGE, $loId, $author->id);
                            }
                        }

                        break;

                    case Schema::O_ENROLMENT:
                        break;
                }
            }
        }
    }

    protected function importEnrolment(Connection $go1, $object)
    {
        $go1->update('gc_user', ['profile_id' => $object->profile_id], ['id' => $object->metadata->user_id]);

        $enrolmentId = $mock->createEnrolment($go1, [
            'id'                => $object->id,
            'lo_id'             => $object->lo->id,
            'profile_id'        => $object->profile_id,
            'status'            => EnrolmentStatuses::toString($object->status),
            'taken_instance_id' => $object->metadata->instance_id,
        ]);

        if (!empty($object->assessors)) {
            foreach ($object->assessors as $assessorId) {
                $mock->link(
                    $go1,
                    EdgeTypes::HAS_TUTOR_ENROLMENT_EDGE,
                    $assessorId,
                    $enrolmentId
                );
            }
        }
    }
}
