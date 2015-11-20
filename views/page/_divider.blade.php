<?php
$extractId = (isset($group))? $group->id : $element->id;
$extractLabel = (isset($group))? "Group" : "divider";
$extractPip = (isset($pip))? $pip : "page";
?>

<div class="form-group">
	<div class="col-sm-12 col-xs-12">
		<div class="infuseDivider infusePageSerialize"
			data-serialize-tag="divider"
			data-serialize-page-value="1"
			data-serialize-page-value-name="{{$element->name}}"
			data-serialize-page-value-description="{{$element->description}}"
			data-serialize-page-value-id="{{$element->id}}"
			data-clipboard-text="InfusePage::extract({{$infusePage->id}}, '{{$extractPip}}', '{{$extractId}}', '{{$extractLabel}}')">
		</div>
	</div>
</div>
