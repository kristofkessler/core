<?php
/**


 */
namespace SearchKeys\View\Helper\SearchKeys;
use VuFind\Search\Options\PluginManager as OptionsManager;

class SearchBox extends \VuFind\View\Helper\Root\SearchBox
{
    /**
     * Configuration for search box.
     *
     * @var array
     */
    protected $searchkeysConfig;

    /**
     * Constructor
     *
     * @param OptionsManager $optionsManager    Search options plugin manager
     * @param array          $config            Configuration for search box
     * @param array          $searchkeysConfig  Configuration for searchkeys
     * @param array          $placeholders      Array of placeholders keyed by
     * backend
     * @param array          $alphabrowseConfig source => label config for
     * alphabrowse options to display in combined box (empty for none)
     */
    public function __construct(OptionsManager $optionsManager, $config = [],
        $searchkeysConfig = [], $placeholders = [], $alphabrowseConfig = []
    ) {
        $this->optionsManager = $optionsManager;
        $this->config = $config;
        $this->searchkeysConfig = $searchkeysConfig;
        $this->alphabrowseConfig = $alphabrowseConfig;
        $this->placeholders = $placeholders;
    }

    public function getHandlers($activeSearchClass, $activeHandler)
    {
        $handlers = [];
        $keyClass = 'keys-' . strtolower($activeSearchClass);
        $searchKeys = [];
        if (isset($this->searchkeysConfig[$keyClass])) {
            $searchKeys = array_unique($this->searchkeysConfig[$keyClass]);
        }
        foreach( $searchKeys as $key => $value) {
            $handlers[] = [
                'value' => $value, 'label' => $value, 'indent' => false, 'selected' => ($activeHandler == $value)
            ];
        }
        return $handlers;
    }

    public function getAdvancedHandlers($activeSearchClass) {
        $handlers = [];
        $keyClass = 'keys-' . strtolower($activeSearchClass);
        foreach($this->searchkeysConfig[$keyClass] as $key => $value) {
            $handlers[$value] = $value;
        }
        return $handlers;
    }

}
?>

