<?php
/* @var $this EmailLogsController */
/* @var $model EmailLogs */
$this->pageTitle=$this->pageTitle() . ' - Admin';
$this->breadcrumbs=array(
	'Email Logs'=>array('index'),
	'Manage',
);

$this->menu=array(
	array('label'=>'List EmailLogs', 'url'=>array('index')),
	array('label'=>'Create EmailLogs', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$('#email-logs-grid').yiiGridView('update', {
		data: $(this).serialize()
	});
	return false;
});
");
?>


<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>

<?php echo CHtml::link('Advanced Search','#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->
 <?php
		$this->beginWidget('zii.widgets.CPortlet', array(
			'title'=>"<h1>Manage Email Logs</h1>",
		));
		
	?>
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'email-logs-grid',
	'itemsCssClass'=>'table table-striped table-bordered table-hover',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'id',
		'template_id',
		'email_to',
		'email_cc',
		'subject',
		/*
		'created',
		'created_by',
		'modified',
		'modified_by',
		*/
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
<?php $this->endWidget();?>