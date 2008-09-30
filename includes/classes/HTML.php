<?php
class HTML {
	/**
	* Creates a dropdown(select) menu using the given array and select what is $selected.
	* Arguments :	$array - The array to be used for the dropdown
	*				$name - The name and ID of the dropdown
	*				$selected - The value that is selected by default - ie. key of the array.
	*				$extra - All the additional attributes that must be added to the &lt;select&gt; tag - as an array.
	* Example	: 
	* $countries = array(
	* 	'US'	=>	'United State of America',
	*	'IN'	=>	'India',
	*	'RU'	=>	'Russia'
	* );
	* buildDropDownArray($list,'countries','IN',array('class'=>'dropdown','multiple'=>'multiple'));
	*/
	function buildDropDownArray($array, $name, $selected="", $extra=array(), $print_select=true) {
		$attributes = '';
		
		$select = $this->getBeginTag("select",$extra + array('name'=>$name,'id'=>$name));// The $extra must go first to make sure that user specified name/id will overwrite the default ones.
		foreach ($array as $key=>$value) {
			$attrbs = array('value'=>$key);
			if($key == $selected) $attrbs['selected']="selected";
			$select .= $this->getTag('option',$attrbs,$value);
		}
		$select .= $this->getEndTag("select");
		
		if($print_select) print $select;
		return $select;
	}
	
	/**
	 * Create a input row with label and an input field.
	 * If its an SELECT input, then you must specify the options array in the $extra array with the index 'options' - like this...
	 * <pre><code class="php">
	 * $html->buildInput("country", "Country", 'select', 'IN', //IN is the selected option
	 *		array('options' => array(
	 * 			'US'	=>	'United State of America',
	 *			'IN'	=>	'India',
	 *			'RU'	=>	'Russia'), 'class'=>'dropdown' //Extra attributes for the select tag.
	 * ));</code></pre>
	 */
	function buildInput($name, $title='', $type='text', $data='', $extra=array(), $info='') {
		global $PARAM;
	
		$title = ($title) ? $title : format($name);
		$tag = 'input';
		$attributes = array(
			'type'	=> $type,
			'name'	=> $name,
			'id'	=> $name,
		);
		$value = '';
		
		if(!$data and isset($PARAM['name'])) $value = $PARAM['name'];
	
		if($type == 'checkbox') { //Checkbox
			$attributes['value'] = '1';
			if($data) {
				$attributes['checked'] = 'checked';
				$attributes['value'] = $data;
			}
			
		} else if ($type == 'textarea' ) { //Textarea
			$tag = 'textarea';
			unset($attributes['type']);
			$attributes['rows'] = 5;
			$attributes['cols'] = 50;
			$value = $data;
		}
		
		if($data) $attributes['value'] = $data;
		elseif(isset($PARAM[$name]) and !isset($attributes['value'])) {
			$attributes['value'] = $value = $PARAM[$name];
		}
	
		//Create all the attributes that is to be appended at the end of the tag.
		$all_attributes = $extra + $attributes;
		if($tag == 'textarea' and isset($all_attributes['value'])) unset($all_attributes['value']); //Textarea don't have a value attribute.
		
		$label = $this->getTag('label', array('for'=>$all_attributes['id']), $title);
		
		if($type == 'select') {
			$options = array();
			if(isset($extra['options'])) {
				$options = $extra['options'];
				unset($extra['options']);
			}
			
			$input = $this->buildDropDownArray($options, $name, $data, $extra, false);
		} else {
			$input = $this->getTag( $tag, $all_attributes, $value );
		}
	
	
		print $label . $input . $info . "<br />\n";
	}
	
	
	//////////////////////////////////////////// Base Stuff //////////////////////////////////
	function getBeginTag($tag , $all_attributes , $end=false) {
		$attributes = '';
		foreach($all_attributes as $attrib=>$value) {
			$attributes .= " $attrib=\"$value\"";
		}
		
		$tag = "<$tag $attributes";
		$tag .= ($end) ? ' />' : '>'; //Auto close the tag?
	
		return $tag;
	}
	/**
	 * Returns the end tag for the tag given as the argument. Will anyone use this?
	 */
	function getEndTag( $tag ) {
		return "</$tag>";
	}
	
	function getTag( $tag, $attributes, $value='') {
		$self_close = false;
		$all_closing_tags = array('img','input','br','iframe');
		if(in_array($tag,$all_closing_tags)) {
			$self_close = true;
		}
		
		if($self_close) {
			$tag = $this->getBeginTag( $tag, $attributes, true);
		} else {
			$tag = $this->getBeginTag( $tag, $attributes ) . $value . $this->getEndTag($tag);
		}
		return $tag;
	}
	
	function buildTag( $tag, $attributes, $value ) {
		print $this->getTag( $tag, $attributes, $value );
	}
}