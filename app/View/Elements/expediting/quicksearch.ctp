<?php
$controller = 'suppliers';
$action = 'quicksearch';
$minLength = 2;
$discription = __('Technical Place',true);

echo $this->Form->create('Quicksearch',array('id' => 'QuicksearchForm','class' => 'quip_search_form'));
echo $this->Form->input('hidden', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => 1,
				)
			);

echo $this->Form->input('this_id', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => 0,
				)
			);

echo $this->Form->input('searching_autocomplet', array(
				'class' =>'autocompletion searching_autocomplet',
				'label' => false,
				'div' => false,
				'title' => $discription,
				'placeholder' => $discription,
				'formaction' => 'autocomplete'
				)
			);

echo $this->Form->input('json_url', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => $this->Html->url(array_merge(array('controller' => $controller,'action' => $action),$this->request->projectvars['VarsArray'])),
			)
		);

echo $this->Form->input('request_url', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => $this->Html->url(array('controller' => $controller,'action' => 'overview')),
			)
		);

echo $this->Form->input('min_length', array(
				'type' =>'hidden',
				'label' => false,
				'div' => false,
				'value' => $minLength,
			)
		);

echo $this->Form->end();

if(isset($this->request->data['AddExpeditingUrl'])){

	echo $this->Html->link(__('Add',true),
		array_merge(
			array(
				'controller' => $this->request->data['AddExpeditingUrl']['controller'],
				'action' => $this->request->data['AddExpeditingUrl']['action'],
			),
			$this->request->data['AddExpeditingUrl']['parm']
		),
	  array(
	    'title' => __('Add',true),
	    'class' =>array('modal icon icon_add'
	    )
	  )
	);

	echo '<br>';
}
	if($this->request->params['action'] == 'index'){
	
		echo $this->Html->link(__('Statistic',true),
			array_merge(
				array('controller'=>'suppliers','action' => 'statistic'),
				array($this->request->projectvars['VarsArray'][0],$this->request->projectvars['VarsArray'][1])
			),
			array(
				'title' => __('Statistic',true),
				'class'=>'icon icon_statistik ajax'
			)
		);
	
	}
?>
<?php echo $this->element('expediting/js/quicksearch',array('SearchFormName' => '#QuicksearchForm'));?>
