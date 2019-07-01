<?php

namespace app\controllers;

use app\models\Category;
use Egulias\EmailValidator\Exception\ExpectingAT;


use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;


class ApiController extends Controller
{
    private function SqlParse($id)
    {
        $data = Category::find()->where(['pid' => $id])->asArray()->all();
        foreach ($data as $category) {
            if ($category['pid'] == $id) {
                $array[$category['name']] = $this->SqlParse($category['id']);
            } else {
                continue;
            }
        }
        return $array;
    }

    private function SqlDelete($id)
    {
        $categories = Category::find()->where(['pid' => $id])->all();
        if (!empty($categories)) {
            foreach ($categories as $category) {
                $this->SqlDelete($category->id);
                $category->delete();
            }
        }

    }


    public function actionGet($name = null)
    {
        try {
            if (empty($name)) {
                $categories = Category::find()->where(['pid' => 0])->asArray()->all();
                foreach ($categories as $category) {
                    if ($category['pid'] == 0)
                        $data[$category['name']] = $this->SqlParse($category['id']);
                }
            } else {
                $category = Category::find()->where(['name' => $name])->asArray()->one();
                $data[$category['name']] = $this->SqlParse($category['id']);
            }

            return json_encode($data,JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return 'Ошибка';
        }
    }

    public function actionCreate()
    {
        try {
            $request = Yii::$app->request;
            $category = new Category();
            $category->name = $request->get('name');
            if (!empty($request->get('ownername'))) {
                $Owner = Category::find()->where(['name' => $request->get('ownername')])->one();
                $category->pid = $Owner->id;
            } else {
                $category->pid = 0;
            }
            $category->save();
            $this->actionGet();
        } catch (\Exception $e) {
            return 'Ошибка';
        }

    }

    public function actionMove()
    {
        try {
            $request = Yii::$app->request;
            $category = Category::find()->where(['name' => $request->get('newcategory')])->one();
            Category::updateAll(['pid' => $category->id], ['=', 'name', $request->get('name')]);
            $this->actionGet();
        } catch (\Exception $e) {
            return 'Ошибка';
        }
    }

    public function actionDelete()
    {
        try {
            $request = Yii::$app->request;
            $category = Category::find()->where(['name' => $request->get('name')])->one();
            $this->SqlDelete($category->id);
            $category->delete();
            $this->actionGet();
        } catch (\Exception $e) {
            return 'Ошибка';
        }
    }


}
