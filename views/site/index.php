<?php

/* @var $this yii\web\View */

$this->title = 'Citing Bot | Главная страница';
?>
<div class="site-index">

    <form action="<?= \yii\helpers\Url::to(['api/index']) ?>">

        <div class="form-group">
            <label for="input-pmid">PMID</label>
            <input class="form-control" name="pmid" id="input-pmid" placeholder="PMID">
        </div>

        <div class="form-group">
            <label for="input-doi">DOI</label>
            <input class="form-control" name="doi" id="input-doi" placeholder="DOI">
        </div>

        <button type="submit" class="btn">Отправить</button>
    </form>
</div>
