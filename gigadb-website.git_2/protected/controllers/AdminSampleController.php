<?php

class AdminSampleController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow', // admin only
				'actions'=>array('admin','delete','index','view','create','update'),
				'roles'=>array('admin'),
			),
                        array('allow', 'actions' => array('create1', 'choose'), 'users' => array('@')),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Sample;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Sample']))
		{
			$model->attributes=$_POST['Sample'];
                        
                        $array = explode(":",$_POST['Sample']['species_id']);
                        $tax_id=$array[0];
                        $species = Species::model()->findByAttributes(array('tax_id' => $tax_id));
                        $model->species_id=$species->id;
                        $model->attributesList = $_POST['Sample']['attributesList'];
                        
            //              if(strstr($_POST['Sample']['code'],':'))
            //     {
            //     //$attribute_temp=null;
            //     $temp=explode(':', $_POST['Sample']['code']);
            //     if($temp[0]=='SAMPLE')
            //     {
            //         $xmlpath=  'http://www.ebi.ac.uk/ena/data/view/'."$temp[1]".'&display=xml';
            //         $allfile= simplexml_load_file($xmlpath);
                    
                   
                    
            //     foreach ($allfile->SAMPLE->SAMPLE_ATTRIBUTES->SAMPLE_ATTRIBUTE as $child)
            //     {
            //         if($child->TAG=='Sample type'||$child->TAG=='Time of sample collection'||$child->TAG=='Habitat'||$child->TAG=='Sample extracted from')
            //             $attribute_temp.= $child->TAG." = "."\"".$child->VALUE."\", ";
            //     }
            //     //$attribute_temp.="Description = "."\"".$allfile->SAMPLE->DESCRIPTION."\", ";
                      
            //     }
            //     //$model->s_attrs=$attribute_temp;
            // }
                    if($model->save()) {
                        $this->updateSampleAttributes($model);
                        $this->redirect(array('view','id'=>$model->id));
                    }
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}
         
        
        public function actionCreate1() {
            $model = new Sample;

// Uncomment the following line if AJAX validation is needed
// $this->performAjaxValidation($model);

              if (isset($_POST['Sample'])) {
            $model->attributes = $_POST['Sample'];
            if ($model->save())
                $this->redirect('/adminDatasetSample/create1');
        }

        $this->render('create1', array(
            'model' => $model,
        ));
    }
     public function storeDataset() {
        if (isset($_SESSION['dataset']) && isset($_SESSION['images'])) {
            $dataset = new Dataset;
            $dataset->image = new Images;
            $result = Dataset::model()->findAllBySql("select identifier from dataset order by identifier desc limit 1;");
            $max_doi = $result[0]->identifier;

            $identifier = $max_doi + 1;

            $dataset_id = 0;

            $dataset->attributes = $_SESSION['dataset'];
            $dataset->image->attributes = $_SESSION['images'];

            $dataset->identifier = $identifier;

            $dataset->dataset_size = 0;
            $dataset->ftp_site = "";
            if ($dataset->publication_date == "")
                $dataset->publication_date = null;
            if ($dataset->modification_date == "")
                $dataset->modification_date = null;


            if ($dataset->image->validate('update') && $dataset->validate('update') && $dataset->image->save()) {
                // save image
                $dataset->image_id = $dataset->image->id;

                if ($dataset->save()) {
                    $dataset_id = $dataset->id;
                    // link datatypes
                    if (isset($_SESSION['datasettypes'])) {
                        $datasettypes = $_SESSION['datasettypes'];
                        foreach ($datasettypes as $id => $datasettype) {
                            $newDatasetTypeRelationship = new DatasetType;
                            $newDatasetTypeRelationship->dataset_id = $dataset->id;
                            $newDatasetTypeRelationship->type_id = $id;
                            $newDatasetTypeRelationship->save();
                        }
                    }
                }
            }
            return array($dataset_id, $identifier);
        }
    }

    public function actionChoose() {
        $model = new Sample('search');
        $model->unsetAttributes();  // clear any default values
        if (isset($_GET['samples'])) {

            $result = $this->storeDataset();
            $dataset_id = $result[0];

            $samples_id = $_GET['samples'];
            $samples_array = explode(",", $samples_id);


            foreach ($samples_array as $key => $value) {
                $datasetSample = new DatasetSample;
                $datasetSample->dataset_id = $dataset_id;
                $datasetSample->sample_id = $value;
                if ($datasetSample->save()) {
                    
                }
            }

            $this->redirect(array('/dataset/' . $result[1]));
        }


//        if (isset($_POST['DatasetSample'])) {
//            $model->attributes = $_POST['DatasetSample'];
//            if ($model->save())
//                $this->redirect(array('view', 'id' => $model->id));
//        }

        if (isset($_GET['Sample']))
            $model->attributes = $_GET['Sample'];


//$model->getPagination()->pageSize = $model->count();
        $this->render('choose', array(
            'model' => $model,
        ));
    }

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);
                //$old_code= $model->code;
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Sample']))
		{
			$model->attributes = $_POST['Sample'];

            if (strpos($_POST['Sample']['species_id'], ":") !== false) {
                $array = explode(":", $_POST['Sample']['species_id']);
//                var_dump($array);
                $tax_id = $array[0];
                if (is_numeric($tax_id)) {
                    $species = Species::model()->findByAttributes(array('tax_id' => $tax_id));
                    $model->species_id = $species->id;
                    
                    // save sample attributes
                    $model->attributesList = $_POST['Sample']['attributesList'];
                    $this->updateSampleAttributes($model);
                    
                    if ($model->save())
                    {
                        // $dataset_id= DatasetSample::model()->findByAttributes(array('sample_id'=>$model->id))->dataset_id;
                        //     $files= File::model()->findAllByAttributes(array('code'=>$old_code,'dataset_id'=>$dataset_id));
                        //     foreach($files as $file)
                        //     {
                        //         $file->code=$model->code;
                        //         $file->save();
                        //         //echo $model->code;
                        //     }
                        $this->redirect(array('view', 'id' => $model->id));
                    }
                    
                }
                else {
                    $model->addError("error", 'The species id should be numeric');
                }
            } else {
                $model->addError("error", 'The input format is wrong, should be id:common_name');
            }
		}
                
            $specie = Species::model()->findByPk($model->species_id);
            $this->render('update',array(
                'model' => $model,
                'specie' => $specie,
            ));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		if(Yii::app()->request->isPostRequest)
		{
			// we only allow deletion via POST request
			$this->loadModel($id)->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Sample');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Sample('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Sample'])) {
			$attrs = $_GET['Sample'];
			$model->setAttributes($attrs, true);
		}

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Sample::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='sample-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
        
    /**
     * Upate sample attribute
     * 
     * @param Sample $model
     */
    private function updateSampleAttributes($model)
    {
        // delete first all the sample Attribute
        SampleAttribute::model()->deleteAllByAttributes(array('sample_id' => $model->id));
        
        if (trim($model->attributesList)) {
            
            // From a model we will clone
            $sampleAttr = new SampleAttribute();
            $sampleAttr->sample_id = $model->id;
            
            foreach (explode(',', $model->attributesList) as $sAttr) {
                $sAttr = str_replace('"', '', $sAttr);
                $data = explode('=', $sAttr);
                if (count($data) == 2) {
                    
                    // Get attribute, if not exist we create
                    $attribute = Attribute::model()->findByAttributes(array('structured_comment_name' => trim($data[0])));
                    if (!$attribute) {
                        $attribute = new Attribute();
                        $attribute->structured_comment_name = trim($data[0]);
                        $attribute->attribute_name = str_replace('_', ' ', trim($data[0]));
                        $attribute->definition = str_replace('_', ' ', trim($data[0]));
                        $attribute->save();
                    }
                    
                    // Let's save the new sample attribute
                    $sampleAttribute = clone $sampleAttr;
                    $sampleAttribute->value = trim($data[1]);
                    $sampleAttribute->attribute_id = $attribute->id;
                    $sampleAttribute->save();
                }
            }
        }
    }
}
