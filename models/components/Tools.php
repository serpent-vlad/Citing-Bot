<?php

namespace models\components;

use Yii;
use yii\base\Exception;
use models\components\externalDb\Doi;
use models\components\externalDb\Pubmed;

/**
 * Class Tools
 *
 * @property $input    string
 * @property $output   string
 * @property $scenario int
 *
 * @package models\components
 */
class Tools
{
    const SCENARIO_PMID = 10;
    const SCENARIO_DOI = 20;

    public $scenario = false;

    public $input = '';

    public $output = '';

    protected $attributes;

    /**
     * Tools constructor.
     *
     * @param bool $type
     *
     * @throws Exception
     */
    public function __construct($type = false)
    {
        if (!$type) {
            throw new Exception('Fatal error in function <Tools/construct>: not found $type');
        }
    }

    /**
     * Get the short hash of the currently checked-out Git commit.
     * @return string
     */
    public static function gitShortHash()
    {
        return Yii::$app->cache->getOrSet('shortHashGit', function () {
            return exec("git rev-parse --short HEAD");
        }, 0);
    }

    /**
     * Get the full hash of the currently checkout-out Git commit.
     * @return string
     */
    public static function gitHash()
    {
        return Yii::$app->cache->getOrSet('hashGit', function () {
            return exec("git rev-parse HEAD");
        }, 0);
    }

    /**
     * Get the formatted real memory usage.
     * @return float
     */
    public static function requestMemory()
    {
        $mem = memory_get_usage(false);
        $div = pow(1024, 2);

        return number_format($mem / $div, 2);
    }

    /**
     * Get the duration of the current HTTP request in seconds.
     * @return double
     * Untestable since there is no request stack in the tests.
     * @codeCoverageIgnore
     */
    public static function requestTime()
    {
        return number_format(Yii::getLogger()->getElapsedTime(), 3);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function read()
    {
        if (!$this->scenario) {
            return false;
        }

        if ($this->scenario === Tools::SCENARIO_PMID) {
            $api = new Pubmed($this->input);
        } elseif ($this->scenario === Tools::SCENARIO_DOI) {
            $api = new Doi($this->input);
        } else {
            throw new Exception('Fatal error in function <Tools/read>: not found self::$scenario');
        }

        $api->getInfoById();
        $this->attributes = $api->attributes;

        return true;
    }

    public function getOutputTemplate()
    {
        $template = new Template();
        $template->attributes = $this->attributes;

        $this->output = $template->getOutput($this->scenario);

        return $this->output;
    }
}