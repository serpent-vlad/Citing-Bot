<?php

namespace app\controllers;

use models\components\Tools;
use models\components\wiki\wikiTools;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only'  => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'scenario' => '',
            'doi'      => 2
        ]);
    }

    /**
     * Displays Bot page.
     *
     * @param null|string|int $pmid
     * @param null|string     $doi
     * @return string
     */
    public function actionApi($pmid = 1542678, $doi = null)
    {
        $tools = new Tools('api');

        /* is PMID */
        if ($pmid != null && (int)$pmid > 0) {
            $tools->scenario = Tools::SCENARIO_PMID;
            $tools->input = $pmid;
            $doi = null;
        }

        /* is DOI */
        if ($doi != null && preg_match('~(10\.\d{3,4}(?:(\.\d+)+|)(/|%2[fF])..+)~', $doi)) {
            $tools->scenario = Tools::SCENARIO_DOI;
            $tools->input = $doi;
            $pmid = null;
        }

        /* is true input */
        if ($pmid != null || $doi != null) {
            $tools->read();
        }

        $output = $tools->getOutputTemplate();

        return $this->render('index', [
            'output' => $output,
        ]);
    }

    public function actionTest()
    {
        $wiki = new wikiTools();

        $w = $wiki->fetch([
            'action'   => 'query',
            'list'     => 'categorymembers',
            'format'   => 'json',
            'cmpageid' => 4975457,
            'cmlimit'  => 50,
        ]);

        $tw = $wiki->fetch([
            'action'  => 'query',
            'prop'    => 'revisions',
            'rvprop'  => 'content',
            'rvlimit' => 1,
            'pageids' => 4975457,
        ]);

        echo "<pre>";
        print_r($w);
        print_r($tw);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
