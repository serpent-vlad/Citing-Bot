<?php

namespace models\components;

use models\components\externalDb\Doi;
use models\components\externalDb\Pubmed;
use yii\base\Exception;

class Tools
{
    const SCENARIO_PMID = 10;
    const SCENARIO_DOI = 20;

    public $scenario = false;

    public $input = '';

    public $output = '';

    protected $attributes;

    public function __construct($type = false)
    {
        if (!$type) {
            throw new Exception('Fatal error in function <Tools/construct>: not found $type');
        }
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