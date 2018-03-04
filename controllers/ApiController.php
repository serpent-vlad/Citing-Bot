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
     * @param bool        $pmid
     * @param bool        $doi
     * @param bool|string $pageOut
     * @return \yii\web\Response
     */
    public function actionIndex($pmid = false, $doi = false, $pageOut = false)
    {
        if ($pmid != null && (int)$pmid > 0 && !$doi) {
            return $this->redirect(Url::to(['pmid', 'pmid' => $pmid, 'pageOut' => $pageOut]));
        }

        if ($doi != null && preg_match('~(10\.\d{3,4}(?:(\.\d+)+|)(/|%2[fF])..+)~', $doi) && !$pmid) {
            return $this->redirect(Url::to(['doi', 'doi' => $doi, 'pageOut' => $pageOut]));
        }

        return $this->goBack();
    }

    /**
     * Displays Bot page for PMID.
     *
     * @param null|string|int $pmid
     * @param bool|string     $pageOut
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionPmid($pmid = 0, $pageOut = false)
    {

        if ((int)$pmid < 1) {
            Yii::$app->response->statusCode = 404;
            return $this->render('empty', [
                'params' => [
                    'PMID',
                ],
            ]);
        }

        if ($pageOut) {
            $refSummary = 'Новая подстраница шаблона {{Cite pmid}} для статьи [[' . $pageOut . ']]';
        } else {
            $refSummary = 'Создана новая подстраница шаблона {{Cite pmid}}';
        }

        $tools = new Tools('api');
        $tools->scenario = Tools::SCENARIO_PMID;
        $tools->input = $pmid;

        $tools->read();
        $output = $tools->getOutputTemplate();

        $wiki = new wikiTools();
        $editPageResult = $wiki->writePage('Шаблон:Cite pmid/' . $pmid, $output, $refSummary);

        return $this->render('pmid', [
            'isEditSuccess' => $editPageResult,
            'output'        => $output,
            'pmid'          => $pmid,
        ]);
    }

    /**
     * @param string $doi
     * @param bool   $pageOut
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionDoi($doi = '', $pageOut = false)
    {
        $doi = (string)urldecode($doi);

        if (!preg_match('~(10\.\d{3,4}(?:(\.\d+)+|)(/|%2[fF])..+)~', $doi)) {
            Yii::$app->response->statusCode = 404;
            return $this->render('empty', [
                'params' => [
                    'DOI',
                ],
            ]);
        }

        if ($pageOut) {
            $refSummary = 'Новая подстраница шаблона {{Cite doi}} для статьи [[' . $pageOut . ']]';
        } else {
            $refSummary = 'Создана новая подстраница шаблона {{Cite doi}}';
        }

        $tools = new Tools('api');
        $tools->scenario = Tools::SCENARIO_DOI;
        $tools->input = $doi;

        $tools->read();
        $output = $tools->getOutputTemplate();

        $wiki = new wikiTools();
        $editPageResult = $wiki->writePage('Шаблон:Cite doi/' . $doi, $output, $refSummary);

        return $this->render('doi', [
            'isEditSuccess' => $editPageResult,
            'output'        => $output,
            'doi'           => $doi,
        ]);
    }
}
