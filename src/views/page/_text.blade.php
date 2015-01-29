<? 
$extractId = (isset($group))? $group->id : $element->id;
$extractLabel = (isset($group))? "Group" : "Text";
$extractPip = (isset($pip))? $pip : "page";
?>

<div class="form-group">
	<div class="col-sm-12 col-xs-12">
		<div class="input-group">
			<span class="input-group-addon">{{$element->name}}</span>
			<textarea class="infusePagesCkeditor form-control infusePageSerialize textareaEditEvent"  
				data-serialize-tag="text" 
				data-serialize-page-value="1" 
				data-serialize-page-value-name="{{$element->name}}" 
				data-serialize-page-value-description="{{$element->description}}" 
				data-serialize-page-value-id="{{$element->id}}">
				{{$element->value}}
			</textarea>
		</div>
		<span class="glyphicon glyphicon-paperclip" data-clipboard-text="InfusePage::extract({{$infusePage->id}}, '{{$extractPip}}', '{{$extractId}}', '{{$extractLabel}}')"></span>
		<div class="infuseLabels">
			<span class="label label-default-bryan">{{$element->description}}</span>
		</div>
	</div>
</div>