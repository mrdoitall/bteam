<?php
/**
 * Created by PhpStorm.
 * User: quan
 * Date: 10/30/18
 * Time: 18:05
 */

namespace console\controllers;


use common\models\AccessAction;
use common\models\AccessActionGroup;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

class ActionController extends Controller
{
    function saveGroup($groupId)
    {
        $groupName = ucfirst(self::charConvertName($groupId));
        $groupId = strtolower(self::charConvert($groupId));
        $group = AccessActionGroup::findOne(['id' => $groupId]);
        if (empty($group)) {
            $group = new AccessActionGroup();
            $group->name = $groupName;
            $group->id = $groupId;
            echo 'Save group ' . $groupId . ' : ' . $group->save(false) . PHP_EOL;
        }
    }

    static function charConvertName($input)
    {
        return preg_replace('/([A-Z0-9]{1})/', ' ${1}', lcfirst($input));
    }

    static function charConvert($input)
    {
        return strtolower(preg_replace('/([A-Z0-9]{1})/', '-${1}', lcfirst($input)));
    }

    function saveAction($actionId, $groupId)
    {
        $actionName = ucfirst(self::charConvertName($actionId));
        $actionId = strtolower(self::charConvert($actionId));
        $groupId = strtolower(self::charConvert($groupId));

        $action = AccessAction::findOne(['id' => $groupId . '/' . $actionId]);
        if (empty($action)) {
            $action = new AccessAction();
            $action->name = $actionName;
            $action->id = $groupId . '/' . $actionId;
            $action->access_action_group_id = $groupId;
            echo 'Save action ' . $groupId . '/' . $actionId . ' : ' . $action->save(false) . PHP_EOL;
        }
    }

    function actionScan()
    {
        $rootDir = dirname(dirname(__DIR__));
        $listControllerFolder = [
            'backend/controllers'
        ];
        $this->saveGroup('*');
        $this->saveAction('*', '*');
        foreach ($listControllerFolder as $controllerFolder) {
            echo 'scan folder ' . $controllerFolder . PHP_EOL;
            $listControllerFolder = $rootDir . '/' . $controllerFolder;
            $files = array_diff(scandir($listControllerFolder), array('..', '.'));
            foreach ($files as $file) {
//                echo 'scan ' . $file . PHP_EOL;
                $filePath = $listControllerFolder . '/' . $file;
                $content = file_get_contents($filePath);
                preg_match_all("/class (.*)Controller extends/", $content, $controllers);
                if (isset($controllers[1][0])) {

                    $className = str_replace('/', "\\", $controllerFolder . '/' . $controllers[1][0] . 'Controller');
                    $classVars = get_class_vars($className);
                    $isPublic = ArrayHelper::getValue($classVars, 'isPublic', false);
                    $isProtect = ArrayHelper::getValue($classVars, 'isProtect', false);
                    $publicActions = ArrayHelper::getValue($classVars, 'publicActions', []);
                    $protectActions = ArrayHelper::getValue($classVars, 'protectActions', []);

                    if ($isPublic || $isProtect) {
                        continue;
                    }

                    preg_match_all("/function action(.*)\(/", $content, $actions);
                    if (isset($actions[1])) {
                        if (count($actions[1]) > 0) {
                            $this->saveGroup($controllers[1][0]);
                            if (count($actions[1]) > 1) {
                                $this->saveAction('*', $controllers[1][0]);
                            }
                            foreach ($actions[1] as $action) {
                                var_dump(self::charConvert($action));
                                if (!in_array(self::charConvert($action), $publicActions) && !in_array(self::charConvert($action), $protectActions)) {
                                    $this->saveAction($action, $controllers[1][0]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}