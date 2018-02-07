<?php

namespace go1\util_dataset\staff\es_dumper;

use Elasticsearch\Client;
use go1\util\es\Schema;
use go1\util_es\Scroll;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Search;

/**
 * GET #staff/devel/php
 *
 * echo \go1\util_dataset\staff\es_dumper\ElasticSearchPortalDumper::dump($app['go1.client.es'], 500592);
 */
class ElasticSearchPortalDumper
{
    public static function dump(Client $client, int $portalId)
    {
        $_ = Scroll::scroll($client, [
            'index' => Schema::INDEX,
            'type'  => Schema::O_PORTAL,
            'body'  => (new Search)
                ->addQuery(new TermQuery('id', $portalId))
                ->toArray(),
        ]);

        foreach ($_ as $row) {
            $portal = &$row['_source'];

            # TODO: Export portal configurations
            # -------
            /*
            $__ = Scroll::scroll($client, [
                'index' => Schema::portalIndex($portalId),
                'type'  => Schema::O_CONFIG,
                'body'  => (new Search)
                    ->addQuery(new TermQuery('instance', $row['title']))
                    ->toArray(),
            ]);

            foreach ($__ as $_row) {
                dump($_row);
            }
            */

            $portals[$portal['id']] = $portal;
        }

        return json_encode(array_values($portals ?? []));
    }
}
