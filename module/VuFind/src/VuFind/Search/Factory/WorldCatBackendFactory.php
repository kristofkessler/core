<?php

/**
 * Factory for WorldCat backends.
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2013.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  Search
 * @author   David Maus <maus@hab.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
namespace VuFind\Search\Factory;

use VuFindSearch\Backend\BackendInterface;
use VuFindSearch\Backend\WorldCat\Backend;
use VuFindSearch\Backend\WorldCat\Connector;
use VuFindSearch\Backend\WorldCat\QueryBuilder;
use VuFindSearch\Backend\WorldCat\Response\XML\RecordCollectionFactory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for WorldCat backends.
 *
 * @category VuFind
 * @package  Search
 * @author   David Maus <maus@hab.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org Main Site
 */
class WorldCatBackendFactory implements FactoryInterface
{
    /**
     * Logger.
     *
     * @var Zend\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Superior service manager.
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * VuFind configuration
     *
     * @var \Zend\Config\Config
     */
    protected $config;

    /**
     * WorldCat configuration
     *
     * @var \Zend\Config\Config
     */
    protected $wcConfig;

    /**
     * Create the backend.
     *
     * @param ServiceLocatorInterface $serviceLocator Superior service manager
     *
     * @return BackendInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator->getServiceLocator();
        $this->config = $this->serviceLocator->get('VuFind\Config')->get('config');
        $this->wcConfig = $this->serviceLocator
            ->get('VuFind\Config')->get('WorldCat');
        if ($this->serviceLocator->has('VuFind\Logger')) {
            $this->logger = $this->serviceLocator->get('VuFind\Logger');
        }
        $connector = $this->createConnector();
        $backend   = $this->createBackend($connector);
        return $backend;
    }

    /**
     * Create the WorldCat backend.
     *
     * @param Connector $connector Connector
     *
     * @return Backend
     */
    protected function createBackend(Connector $connector)
    {
        $backend = new Backend($connector, $this->createRecordCollectionFactory());
        $backend->setLogger($this->logger);
        $backend->setQueryBuilder($this->createQueryBuilder());
        return $backend;
    }

    /**
     * Create the WorldCat connector.
     *
     * @return Connector
     */
    protected function createConnector()
    {
        $wsKey = isset($this->config->WorldCat->apiKey)
            ? $this->config->WorldCat->apiKey : null;
        $connectorOptions = isset($this->wcConfig->Connector)
            ? $this->wcConfig->Connector->toArray() : [];
        $connector = new Connector(
            $wsKey, $this->serviceLocator->get('VuFind\Http')->createClient(),
            $connectorOptions
        );
        $connector->setLogger($this->logger);
        return $connector;
    }

    /**
     * Create the WorldCat query builder.
     *
     * @return QueryBuilder
     */
    protected function createQueryBuilder()
    {
        $exclude = isset($this->config->WorldCat->OCLCCode)
            ? $this->config->WorldCat->OCLCCode : null;
        return new QueryBuilder($exclude);
    }

    /**
     * Create the record collection factory
     *
     * @return RecordCollectionFactory
     */
    protected function createRecordCollectionFactory()
    {
        $manager = $this->serviceLocator->get('VuFind\RecordDriverPluginManager');
        $callback = function ($data) use ($manager) {
            $driver = $manager->get('WorldCat');
            $driver->setRawData($data);
            return $driver;
        };
        return new RecordCollectionFactory($callback);
    }
}
