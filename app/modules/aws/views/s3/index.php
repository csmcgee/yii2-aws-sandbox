<?php
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\helpers\Html;

\Yii::setAlias('@awsS3', Url::ensureScheme($result['effectiveUri'], 'https'));
?>

<?php if (Yii::$app->session->hasFlash('fileUploaded')): ?>

<div class="alert alert-success">
    File uploaded successfully.
</div>

<?php endif; ?>

<div>
    <div>
        <?php foreach($folders as $folder): ?>
            <a href="<?= Url::current(['folder' => $folder]) ?>"><?= $folder ?></a>
        <?php endforeach; ?>
    </div>
    <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'Key',
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{download}',
                    'buttons' => [
                        'download' => function($url, $model) {
                            return Html::a('<span class="glyphicon glyphicon-arrow-down"></span>',
                                Url::to(['/aws/s3/download', 'key' => $model['Key']]),
                                [
                                    'title' => 'Download',
                                    'data-pjax' => '0'
                                ]
                            );
                        }
                    ]
                ]

            ]
        ]);
    ?>
</div>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

    <?= $form->field($model, 'file')->fileInput() ?>

    <button>Submit</button>

<?php ActiveForm::end() ?>