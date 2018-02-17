<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use models\components\Tools;
use models\components\wiki\wikiTools;

class ApiController extends Controller
{
    /**
     * @param bool $pmid
     * @param bool $doi
     * @param bool $createTemplate
     * @return \yii\web\Response
     */
    public function actionIndex($pmid = false, $doi = false, $createTemplate = false)
    {
        if ($pmid != null && (int)$pmid > 0 && !$doi) {
            return $this->redirect(Url::to(['pmid', 'pmid' => $pmid, 'createTemplate' => $createTemplate]));
        }

        if ($doi != null && preg_match('~(10\.\d{3,4}(?:(\.\d+)+|)(/|%2[fF])..+)~', $doi) && !$pmid) {
            return $this->redirect(Url::to(['doi', 'doi' => $doi, 'createTemplate' => $createTemplate]));
        }

        return $this->goBack();
    }

    /**
     * Displays Bot page for PMID.
     *
     * @param null|string|int $pmid
     * @param bool            $createTemplate
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionPmid($pmid = 0, $createTemplate = false)
    {

        if ((int)$pmid < 1) {
            Yii::$app->response->statusCode = 404;
            return $this->render('empty', [
                'params' => [
                    'PMID',
                ],
            ]);
        }

        $tools = new Tools('api');
        $tools->scenario = Tools::SCENARIO_PMID;
        $tools->input = $pmid;

        $tools->read();
        $output = $tools->getOutputTemplate();

        if ((bool)$createTemplate) {
            $wiki = new wikiTools();

            $editPageResult = $wiki->writePage('Участник:Serpent Vlad/temp/песочница1', $output);

            if (!$editPageResult) {
                echo 'Error!';
                exit;
            }
        }

        return $this->render('pmid', [
            'output' => $output,
        ]);
    }

    public function actionDoi($doi = 0, $createTemplate = false)
    {

        if (!preg_match('~(10\.\d{3,4}(?:(\.\d+)+|)(/|%2[fF])..+)~', (string)$doi)) {
            Yii::$app->response->statusCode = 404;
            return $this->render('empty', [
                'params' => [
                    'DOI',
                ],
            ]);
        }

        $tools = new Tools('api');
        $tools->scenario = Tools::SCENARIO_DOI;
        $tools->input = $doi;

        $tools->read();
        $output = $tools->getOutputTemplate();

        if ((bool)$createTemplate) {
            $wiki = new wikiTools();

            $editPageResult = $wiki->writePage('Участник:Serpent Vlad/temp/песочница1', $output);

            if (!$editPageResult) {
                echo 'Error!';
                exit;
            }
        }

        return $this->render('doi', [
            'output' => $output,
        ]);
    }
}
