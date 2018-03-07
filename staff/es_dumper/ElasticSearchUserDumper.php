<?php

namespace go1\util_dataset\staff\es_dumper;

use Elasticsearch\Client;
use go1\util\es\Schema;

/**
 * GET #staff/devel/php
 *
 * echo \go1\util_dataset\staff\es_dumper\ElasticSearchUserDumper::dump($app['go1.client.es'], 500592);
 */
class ElasticSearchUserDumper
{
    public static function dump(Client $client, int $portalId)
    {
        $accounts = \go1\util_dataset\staff\es_dumper\ElasticSearchAccountDumper::dump($client, $portalId);

        foreach ($accounts as $account) {
            $user = $client->get([
                'index'   => Schema::INDEX,
                'type'    => Schema::O_USER,
                'routing' => Schema::INDEX,
                'id'      => $account->metadata->user_id,
            ]);
            $users[] = $user['_source'];
        }

        return json_encode($users ?? []);
    }
}
