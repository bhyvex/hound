<?php
/* @var $this EmailTemplatesController */
/* @var $model EmailTemplates */
$this->pageTitle=$this->pageTitle() . ' - Update';
$this->breadcrumbs=array(
	'Email Templates'=>array('index'),
	$model->title=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List EmailTemplates', 'url'=>array('index')),
	array('label'=>'Create EmailTemplates', 'url'=>array('create')),
	array('label'=>'View EmailTemplates', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage EmailTemplates', 'url'=>array('admin')),
);
?>

<h1><?php //echo $model->id; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>