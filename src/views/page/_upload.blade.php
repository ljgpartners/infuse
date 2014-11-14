<? 
$extractId = (isset($group))? $group->id : $element->id;
$extractLabel = (isset($group))? "Group" : "Upload";
$extractPip = (isset($pip))? $pip : "page";
?>

<div class="form-group">
	<div class="col-sm-12 col-xs-12">
		<div class="input-group">
			<span class="input-group-addon">{{$element->name}}</span>
			<input readonly="readonly" placeholder="no file" class="form-control initialUploadInputEvent" name="name" type="text" value="{{$element->value}}">
			<div class="input-group-btn">
				<span tabindex="-1" class="btn btn-default btn-file">
					Browse…<input class="infusePageSerialize" type="file" name="{{$element->id}}" 
						data-serialize-tag="upload" 
						data-serialize-page-value="1" 
						data-serialize-page-value-name="{{$element->name}}" 
						data-serialize-page-value-description="{{$element->description}}" 
						data-serialize-page-value-id="{{$element->id}}">
				</span>
			</div>
		</div>
		<span class="glyphicon glyphicon-paperclip" data-clipboard-text="InfusePage::extract({{$infusePage->id}}, '{{$extractPip}}', '{{$extractId}}', '{{$extractLabel}}')"></span>
		<div class="infuseLabels">
			<span class="label label-default-bryan">{{$element->description}}</span>
		</div>
	</div>
</div>