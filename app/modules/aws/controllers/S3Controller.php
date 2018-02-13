<?php

namespace app\modules\aws\controllers;

use yii\web\Controller;
use \app\modules\aws\models\UploadForm;
use yii\web\UploadedFile;
use yii\helpers\Url;

/**
 * Default controller for the `aws` module
 */
class S3Controller extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex($folder = '')
    {
        $model = new UploadForm();
        $session = \Yii::$app->session;

        $aws = \Yii::$app->awssdk->getAwsSdk();
        $s3 = $aws->createS3();

        if (\Yii::$app->request->isPost) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($this->upload($model->file)) {
                // file is uploaded successfully
                $session->setFlash('fileUploaded');
                $model = new UploadForm();
            }
        }


        $paginator = $s3->getPaginator('ListObjects', [
            'Bucket' => 'csm-yii2-nv', 
            'Prefix' => $folder,
            'Marker' => $folder,
            'Delimiter' => '/',
            ]
        );

        $items = array();
        $folders = array();
        foreach($paginator as $result) {
            if(!empty($result['CommonPrefixes']))
                foreach($result["CommonPrefixes"] as $folder)
                    $folders[] = $folder["Prefix"];
            if(!empty($result['Contents']))
                foreach($result["Contents"] as $item)
                    $items[] = $item;
        }

        $dataProvider = new \yii\data\ArrayDataProvider([
            'allModels' => $items,
            'pagination' => [
                'pageSize' => 10
            ],
        ]);

        return $this->render('index', ['model' => $model, 'dataProvider' => $dataProvider, 'folders' => $folders]);
    }


    public function actionDownload($key)
    {
        $aws = \Yii::$app->awssdk->getAwsSdk();
        $s3 = $aws->createS3();
        $result = $s3->getObject(['Bucket' => 'csm-yii2-nv', 'Key' => $key]);
        return \Yii::$app->response->sendContentAsFile($result['Body'], $key, ['mimeType' => $result['ContentType']]);
    }

    private function upload($file)
    {
        $aws = \Yii::$app->awssdk->getAwsSdk();
        $s3 = $aws->createS3();
        try {
            $result = $s3->putObject(['Bucket' => 'csm-yii2-nv', 'Key' => $file->name, 'SourceFile' => $file->tempName]);
            return !empty($result);
        } catch(\Exception $e) {
            return false;
        }
    }
}
