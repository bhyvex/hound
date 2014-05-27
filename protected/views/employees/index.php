<?php
/* @var $this EmployeesController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Employees',
);

$this->menu=array(
	array('label'=>'Create Employees', 'url'=>array('create')),
	array('label'=>'Manage Employees', 'url'=>array('admin')),
);
?>

<h1>Employees</h1>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'ajaxUpdate'=>false,
	'itemView'=>'_view',
	
)); ?>
