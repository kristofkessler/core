<?php
/**
 * Record Driver Factory Class loading SolrMarc-Extension for Libraries Module
 *
 * PHP version 5
 *
 * Copyright (C) Staats- und Universitätsbibliothek 2017.
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind2
 * @package  RecordDriver
 * @author   Hajo Seng <hajo.seng@sub.uni-hamburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/subhh/beluga
 */
namespace DependentWorks\RecordDriver;
use Zend\ServiceManager\ServiceManager;

class Factory extends \VuFind\RecordDriver\Factory
{
    /**
     * Factory for SolrMarc record driver.
     *
     * @param ServiceManager $sm Service manager.
     *
     * @return SolrDefault
     */
    public static function getSolrDefault(ServiceManager $sm)
    {
        $driver = new SolrDefault(
            $sm->getServiceLocator()->get('VuFind\Config')->get('config'),
            null,
            $sm->getServiceLocator()->get('VuFind\Config')->get('searches')
        );
        $driver->attachSearchService($sm->getServiceLocator()->get('VuFind\Search'));
        return $driver;
    }
}
