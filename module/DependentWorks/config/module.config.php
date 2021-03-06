<?php
namespace DependentWorks\Module\Configuration;

$config = [
    'controllers' => [
        'factories' => [
            'dependentworksajax' => 'DependentWorks\Controller\Factory::getDependentWorksAjaxController',
        ],
    ],
    'vufind' => [
        'plugin_managers' => [
            'recorddriver' => [
                'abstract_factories' => ['VuFind\RecordDriver\PluginFactory'],
                'factories' => [
                    'solrdefault' => 'DependentWorks\RecordDriver\Factory::getSolrDefault',
                ],
            ],
        ],
    ],
];

return $config;

