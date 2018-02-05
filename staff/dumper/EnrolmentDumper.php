<?php

namespace go1\util_dataset\staff\dumper;

use Elasticsearch\Client;
use go1\util\es\Schema;
use go1\util_es\Scroll;
use ONGR\ElasticsearchDSL\Search;

/**
 * GET #staff/devel/php
 *
 * echo \go1\util_dataset\staff\dump\EnrolmentDumper::dump($app['go1.client.es'], 500592);
 */
class EnrolmentDumper
{
    public static function dump(Client $client, int $portalId)
    {
        $_ = Scroll::scroll($client, [
            'index' => Schema::portalIndex($portalId),
            'type'  => Schema::O_ENROLMENT,
            'body'  => (new Search)->toArray(),
        ]);

        foreach ($_ as $row) {
            $enrollment = $row['_source'];
            $enrollments[$enrollment['id']] = $enrollment;
        }

        return json_encode(!empty($enrollments) ? array_values($enrollments) : []);
    }
}
