<?php


namespace backend\controllers;


use common\models\AccessActionGroup;
use common\models\AccessAssign;
use common\models\AccessRole;
use common\models\UserAccessRole;

class AccessRoleController extends BaseController
{
    public $protectActions = [
        'access-roles',
        'current-assign-role',
        'current-assign-user',
        'access-action-group-for-assign',
    ];

    function actionAccessRoles()
    {
        $accessRoles = AccessRole::find()->all();
        return $this->response(true, $accessRoles);
    }

    function actionSaveAccessRole()
    {
        $accessRole = \Yii::$app->request->post('role');
        if (empty($accessRole)) {
            return $this->responseMessage(false, 'Please enter permission group name');
        }
        $accessRoleDb = empty($accessRole['id']) ? new AccessRole() : AccessRole::findOne([$accessRole['id']]);
        $accessRoleDb->name = $accessRole['name'];
        $accessRoleDb->save(false);
        return $this->success();
    }

    function actionAccessActionGroupForAssign()
    {
        $accessActionGroup = AccessActionGroup::find()->with(['accessActions'])->asArray()->all();
        return $this->response(true, $accessActionGroup);
    }

    function actionSaveRoleAssign()
    {
        $role = \Yii::$app->request->post('role');
        AccessAssign::deleteAll(['access_role_id' => $role['id']]);
        foreach ($role['assign'] as $action => $accept) {
            if ($accept !== true) {
                continue;
            }
            $roleAssign = new AccessAssign();
            $roleAssign->access_action_id = $action;
            $roleAssign->access_role_id = $role['id'];
            $roleAssign->save(false);
        }
        return $this->success();
    }

    function actionSaveUserAssign()
    {
        $user = \Yii::$app->request->post('user');
        AccessAssign::deleteAll(['user_id' => $user['id']]);
        UserAccessRole::deleteAll(['user_id' => $user['id']]);
        foreach ($user['assign'] as $action => $accept) {
            if ($accept !== true) {
                continue;
            }
            $roleAssign = new AccessAssign();
            $roleAssign->access_action_id = $action;
            $roleAssign->user_id = $user['id'];
            $roleAssign->save(false);
        }

        foreach ($user['roles'] as $role => $accept) {
            if ($accept !== true) {
                continue;
            }
            $userAssignRole = new UserAccessRole();
            $userAssignRole->user_id = $user['id'];
            $userAssignRole->access_role_id = $role;
            $userAssignRole->save(false);

        }
        return $this->success();
    }

    function actionCurrentAssignRole()
    {
        $id = \Yii::$app->request->get('id');
        $currentAssignCond = AccessAssign::find()->andWhere(['access_role_id' => $id]);

        $current = [
            '_' => '_'
        ];
        foreach ($currentAssignCond->all() as $item) {
            $current[$item->access_action_id] = true;
        }

        return $this->response(true, $current);
    }

    function actionCurrentAssignUser()
    {
        $id = \Yii::$app->request->get('id');
        $currentAssignCond = AccessAssign::find()->andWhere(['user_id' => $id]);

        $current = [
            '_' => '_'
        ];
        foreach ($currentAssignCond->all() as $item) {
            $current[$item->access_action_id] = true;
        }

        $roles = [
            '_' => '_'
        ];
        $userAcesRoles = UserAccessRole::find()->where(['user_id' => $id])->all();

        foreach ($userAcesRoles as $userAcesRole) {
            $roles[$userAcesRole->access_role_id] = true;
        }

        return $this->response(true, ['assign' => $current, 'roles' => $roles]);
    }
}