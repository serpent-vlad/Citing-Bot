<?php

/* @var $this yii\web\View */

$this->title = 'Empty required ID';
?>
<div class="site-index">

    <h1>Возникла ошибка!</h1>

    <p>Не был указан один из обязательных параметров: <?php foreach ($params as $param): ?><code><?= $param ?></code> <?php endforeach; ?></p>

</div>
