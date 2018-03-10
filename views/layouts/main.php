<?php

/* @var $this \yii\web\View */

/* @var $content string */

use app\widgets\Alert;
use models\components\Tools;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <base href="https://tools.wmflabs.org/citing-bot/"><!--[if IE]></base><![endif]-->
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl'   => Yii::$app->homeUrl,
        'options'    => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    /*echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items'   => [
            ['label' => 'About', 'url' => ['/site/about']],
            ['label' => 'Contact', 'url' => ['/site/contact']],
            Yii::$app->user->isGuest ? (
            ['label' => 'Login', 'url' => ['/site/login']]
            ) : (
                '<li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>'
            )
        ],
    ]);*/
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer navbar-fixed-bottom">
    <div class="container">
        <div class="row">
            <div class="col-xs-7 col-md-9">
                <p>&copy; 2014 – 2018 &middot; <a href="//ru.wikipedia.org/wiki/U:Citing_Bot"
                                                  title="Страница бота" target="_blank">Citing Bot</a> &middot; <a
                            href="//github.com/serpent-vlad/Citing-Bot/releases/tag/<?= Yii::$app->version ?>"
                            target="_blank" title="Текущий релиз Citing-Bot">v.<?= Yii::$app->version ?></a> (<a
                            href="//github.com/serpent-vlad/Citing-Bot/tree/<?= Tools::gitHash() ?>"
                            title="Текущая версия Citing-Bot"
                            target="_blank"><?= Tools::gitShortHash() ?></a>)</p>
                <span>
                    <small>
                        <span>выполнено за <?= Tools::requestTime() ?> сек</span> &middot;
                        <span>использовано <?= Tools::requestMemory() ?> MB памяти</span>
                    </small>
                </span>
            </div>
            <div class="col-xs-5 col-md-3">
                <div class="text-right">
                    <a href="//wikitech.wikimedia.org/wiki/Portal:Cloud_VPS" target="_blank">
                        <img height="40px" src="/citing-bot/img/VPS-badge.svg" alt="Powered by Wikimedia Cloud Services">
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
