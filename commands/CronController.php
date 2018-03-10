<?php

namespace app\commands;

use Yii;
use yii\helpers\Json;
use yii\console\Controller;
use models\components\Tools;
use models\components\wiki\wikiTools;

/**
 * Class CronController
 * @package app\commands
 */
class CronController extends Controller
{
    /**
     * @var int
     */
    public $limit = 50;
    /**
     * @var string Название категории со статьями с необработанным шаблоном Cite pmid
     */
    private $pmidCategory = 'Категория:Википедия:Статьи с необработанным шаблоном Cite pmid';

    /**
     * @param string $actionID
     * @return array|string[]
     */
    public function options($actionID)
    {
        return ['limit'];
    }

    /**
     * @return array
     */
    public function optionAliases()
    {
        return ['l' => 'limit'];
    }

    /**
     * Slug function
     */
    public function actionIndex()
    {
        echo "Wrong action!";
    }

    /**
     * Заполняет необработанные шаблоны Cite pmid
     *
     * @return int
     */
    public function actionPmid()
    {
        $wiki = new wikiTools();

        $categoryJson = Json::decode($wiki->getAllPagesFromCategory($this->pmidCategory, $this->limit));
        if (isset($categoryJson->error)) {
            Yii::warning($categoryJson->error, __METHOD__);
            return 0;
        }

        $pages = $categoryJson->query->categorymembers;
        if (count($pages) === 0) return 0;

        $ids = array_map(function ($page) {
            return $page->pageid;
        }, $pages);

        $resultJson = Json::decode($wiki->getPagesContentById(implode('|', $ids)));
        if (isset($resultJson->error)) {
            Yii::warning($categoryJson->error, __METHOD__);
            return 0;
        }

        $editPageResult = [];
        $pattern = '/{{[ ]?cite(?:[_]?|[ ]*)pmid[ ]?\|[ ]?(\d+)[ ]?(?:\|[ ]?(noedit))?[ ]?}}/is';

        foreach ($resultJson->query->pages as $pageId => $page) {
            preg_match_all($pattern, $page->revisions[0]->{'*'}, $matches);
            foreach ($matches[1] as $matchId => $pmid) {
                $refSummary = 'Новая подстраница шаблона {{Cite pmid}} для статьи [[' . $page->title . ']]';

                $tools = new Tools('api');
                $tools->scenario = Tools::SCENARIO_PMID;
                $tools->input = $pmid;

                $tools->read();
                $output = $tools->getOutputTemplate();

                $editPageResult[$page->title] = $wiki->writePage('Шаблон:Cite pmid/' . $pmid, $output, $refSummary);
            }
        }

        print_r($editPageResult);

        return 1;
    }
}
