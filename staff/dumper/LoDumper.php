<?php

namespace go1\util_dataset\staff\dumper;

use Elasticsearch\Client;
use go1\util\es\Schema;
use go1\util_es\Scroll;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use ONGR\ElasticsearchDSL\Search;

/**
 * GET #staff/devel/php
 *
 * echo \go1\util_dataset\staff\dump\LoDumper::dump($app['go1.client.es'], 500592);
 */
class LoDumper
{
    public static function dump(Client $client, int $portalId, $loTypes = ['course', 'award'])
    {
        $_ = Scroll::scroll($client, [
            'index' => Schema::portalIndex($portalId),
            'type'  => Schema::O_LO,
            'body'  => (new Search)
                ->addQuery(new TermsQuery('type', $loTypes))
                ->toArray(),
        ]);

        foreach ($_ as $row) {
            $lo = &$row['_source'];
            $learningObjects[$lo['id']] = $lo;
        }

        return json_encode(array_values($learningObjects ?? []));
    }
}
