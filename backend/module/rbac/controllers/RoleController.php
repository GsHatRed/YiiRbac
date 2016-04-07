<?php
namespace backend\module\rbac\controllers;

use Yii;
use backend\controllers\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;
use backend\module\rbac\models\AuthItem;
use backend\module\rbac\models\Role;
use backend\module\rbac\models\AuthMenuSearch;
use common\models\Common;

class  RoleController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
                'verbs' => [
                        'class' => VerbFilter::className(),
                        'actions' => [
                                'delete' => ['post'],
                                'bulk-delete' => ['post'],
                        ],
                ],
        ];
    }
    
    /**
     * Lists all AuthItem models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new Role();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        //$searchModel->created_at = Yii::$app->request->get('Role')['created_at'];
        //$searchModel->updated_at = Yii::$app->request->get('Role')['updated_at'];
    
        return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
        ]);
    }
    
    
    /**
     * Displays a single AuthItem model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        if($request->isAjax){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                    'title'=> "角色 #".$model->description,
                    'content'=>$this->renderAjax('view', [
                            'model' => $model,
                    ]),
                    'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                    Html::a('Edit',['update','id'=>$id],['class'=>'btn btn-primary','role'=>'modal-remote'])
            ];
        }else{
            return $this->render('view', [
                    'model' => $model,
            ]);
        }
    }
    
    /**
     * Creates a new AuthItem model.
     * For ajax request will return json object
     * and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $model = new AuthItem(['scenario' => 'createRole']);
        $permissions = AuthMenuSearch::getPemissions();
        if($request->isAjax){
            /*
             *   Process for ajax request
             */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                        'title'=> "新建角色",
                        'content'=>$this->renderAjax('create', [
                                'model' => $model,
                                'permissions' => json_encode($permissions),
                        ]),
                        'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                        Html::button('Save',['class'=>'btn btn-primary sr-only','type'=>"submit",'id'=>'submit']).
                        Html::button('Save',['class'=>'btn btn-primary','type'=>"button", 'onclick'=>'return getChecked(1)'])
    
                ];
            }else if($model->load($request->post()) && $model->validate() && $model->save()){
                return [
                        'forceReload'=>'#crud-datatable-pjax',
                        'title'=> "新建角色",
                        'content'=>'<span class="text-success">Create AuthItem success</span>',
                        'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                        Html::a('创建更多',['create'],['class'=>'btn btn-primary','role'=>'modal-remote'])
    
                ];
            }else{
                return [
                        'title'=> "新建角色",
                        'content'=>$this->renderAjax('create', [
                                'model' => $model,
                                'permissions' => json_encode($permissions),
                        ]),
                        'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                        Html::button('Save',['class'=>'btn btn-primary sr-only','type'=>"submit",'id'=>'submit']).
                        Html::button('Save',['class'=>'btn btn-primary','type'=>"button", 'onclick'=>'return getChecked(1)'])
                ];
            }
        }else{
            /*
             *   Process for non-ajax request
             */
            if ($model->load($request->post()) && $model->validate() && $model->save()) {
                return $this->redirect(['view', 'id' => $model->name]);
            } else {
                return $this->render('create', [
                        'model' => $model,
                        'permissions' => json_encode($permissions),
                ]);
            }
        }
         
    }
    
    /**
     * Updates an existing AuthItem model.
     * For ajax request will return json object
     * and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $model = $this->findModel($id);
        $permissions = AuthMenuSearch::getPemissions($id);
        if($request->isAjax){
            //Set any value for update
            $model->setScenario('updateRole');
            $model->updated_at = $_SERVER['REQUEST_TIME'];
            /*
             *   Process for ajax request
             */
            Yii::$app->response->format = Response::FORMAT_JSON;
            if($request->isGet){
                return [
                        'title'=> "修改角色 #".$model->description,
                        'content'=>$this->renderAjax('update', [
                                'model' => $model,
                                'permissions' => json_encode($permissions),
                        ]),
                        'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                        Html::button('Save',['class'=>'btn btn-primary sr-only','type'=>'submit','id'=>'submit']).
                        Html::button('Save',['class'=>'btn btn-primary','type'=>'button', 'onclick'=>'return getChecked(1)'])
                ];
            }else if($model->load($request->post()) && $model->validate() && $model->save()){
                return [
                        'forceReload'=>'#crud-datatable-pjax',
                        'title'=> "角色 #".$model->description,
                        'content'=>$this->renderAjax('view', [
                                'model' => $model,
                        ]),
                        'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                        Html::a('Edit',['update','id'=>$id],['class'=>'btn btn-primary','role'=>'modal-remote'])
                ];
            }else{
                return [
                        'title'=> "修改角色信息  #".$model->description,
                        'content'=>$this->renderAjax('update', [
                                'model' => $model,
                                'permissions' => json_encode($permissions),
                        ]),
                        'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
                        Html::button('Save',['class'=>'btn btn-primary sr-only','type'=>"submit",'id'=>'submit']).
                        Html::button('Save',['class'=>'btn btn-primary','type'=>"button", 'onclick'=>'return getChecked(1)'])
                ];
            }
        }else{
            /*
             *   Process for non-ajax request
             */
            if ($model->load($request->post()) && $model->validate() && $model->save()) {
                return $this->redirect(['view', 'id' => $model->name]);
            } else {
                return $this->render('update', [
                        'model' => $model,
                        'permissions' => json_encode($permissions),
                ]);
            }
        }
    }
    
    /**
     * Delete an existing AuthItem model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $request = Yii::$app->request;
        $this->findModel($id)->delete();
    
        if($request->isAjax){
            /*
             *   Process for ajax request
             */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];
        }else{
            /*
             *   Process for non-ajax request
             */
            return $this->redirect(['index']);
        }
    
    
    }
    
    /**
     * Delete multiple existing AuthItem model.
     * For ajax request will return json object
     * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionBulkDelete()
    {
        $request = Yii::$app->request;
        $pks = $request->post('pks'); // Array or selected records primary keys
        foreach (AuthItem::findAll(json_decode($pks)) as $model) {
            $model->delete();
        }
    
        if($request->isAjax){
            /*
             *   Process for ajax request
             */
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];
        }else{
            /*
             *   Process for non-ajax request
             */
            return $this->redirect(['index']);
        }
         
    }
    
    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AuthItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AuthItem::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
