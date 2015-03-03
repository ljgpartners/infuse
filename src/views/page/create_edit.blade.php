<?php $breadcrumbCurrentPath = ""; ?>

<div class="sectionNavigation">
	<ul class="nav nav-tabs" role="tablist">
	  @foreach ($infusePages as $p)
			@if ($p->id == $infusePage->id)
				<li class="active">
					<a href="/admin/page/{{$p->id}}">{{$p->title}}</a> 
				</li>
			@else
				<li>
					<a href="/admin/page/{{$p->id}}">{{$p->title}}</a> 
				</li>
			@endif
		@endforeach
	</ul>
	<a href="/admin/page/create" class="addPage"><span class="glyphicon glyphicon-plus"></span></a>
	<div class="sectionInfo">
		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-6">
					<h1 class="quickEdit">
						<span class="infusePageSerialize" data-serialize-tag="pageTitle">
							{{$pageInstance->pageProperties->pageTitle}}
						</span>
						<input class="form-control" type="text" value="{{$pageInstance->pageProperties->pageTitle}}" autocomplete="off">
					</h1>
					<p class="quickEdit">
						<span class="infusePageSerialize" data-serialize-tag="pageDescription">
							{{$pageInstance->pageProperties->pageDescription}}
						</span>
						<input class="form-control" type="text" value="{{$pageInstance->pageProperties->pageDescription}}" autocomplete="off">
					</p>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb pull-right"> 
						@if (isset($breadcrumbs))
							@foreach ($breadcrumbs as $breadcrumb)
								<?php $breadcrumbCurrentPath = $breadcrumbCurrentPath.((!empty($breadcrumbCurrentPath))? ";": "").$breadcrumb['page_instance']; ?>
								<li>
									@if ($breadcrumbCurrentPath == "page")
									<a href="/admin/page/{{$breadcrumb['page_root_id']}}/edit">
									@else
									<a href="/admin/page/{{$breadcrumb['page_root_id']}}/edit?pip={{$breadcrumbCurrentPath}}&rpip=1">
									@endif
										{{$breadcrumb['page_instance_title']}}
									</a>
								</li>
								
							@endforeach
						@endif
					</ol>
				</div>
		  </div>
		</div>
	</div>
	<div class="subPages">
		<?php $pageInstanceNestedPages = $pageInstance->pages; ?> 
		@foreach ($pageInstance->pagesKeys as $id)
		<a href="/admin/page/{{$infusePage->id}}/edit?pip={{$breadcrumbCurrentPath.";".$id}}">{{$pageInstanceNestedPages->{$id}->pageProperties->pageTitle}}</a>
		@endforeach
		<a href="/admin/page/create?pip={{$breadcrumbCurrentPath}}&pri={{$infusePage->id}}" class="addSubPage"><span class="glyphicon glyphicon-plus"></span></a>
	</div>
</div> <!-- end of .sectionNavigation -->

<div class="container-fluid">
	<div class="row">
  	<div class="col-sm-12">
		{{Util::fuseAlerts(Util::flash())}} 
		</div>
	</div>
</div>

<div class="container-fluid infusePage">

	<div class="row developerMenu">
  	<div class="col-sm-2 col-xs-12 ">
  		<a class="inputBuilder draggable addText" href="">Text</a>
  	</div>
  	<div class="col-sm-2 col-xs-12">
  		<a class="inputBuilder draggable addTextBox"  href="">Text Box</a>
  	</div>
  	<div class="col-sm-2 col-xs-12 ">
  		<a class="inputBuilder draggable addUpload" href="">Upload</a>
  	</div>
  	<div class="col-sm-3 col-xs-12 ">
  		<a class="inputBuilder draggable addDivider" href="">Group Divider</a>
  	</div>
  	<div class="col-sm-3 col-xs-12 ">
  		<a class="inputBuilder draggable addGroup" href="">Group</a>
  	</div>
  </div>

  <div class="row">
  	<div class="col-sm-12">


  		<form action="/admin/page{{(isset($infusePage->id))? "/".$infusePage->id : ""}}" class="form-horizontal infusePageForm" method="POST" role="form" enctype="multipart/form-data">


  			<div class="formBlock sortable">

  				<?php $groups = array(); ?>

					@foreach ($pageInstance->pageValues as $element)
						
						@if ($element->type == "string")
							@include('infuse::page._string')
						@elseif ($element->type == "text")
							@include('infuse::page._text')
						@elseif ($element->type == "upload")
							@include('infuse::page._upload')
						@elseif ($element->type == "group")
							<?php array_push($groups, $element); ?>
						@endif

					@endforeach

					<div class="jumbotron hide">
					  <h1>Pages.</h1>
					  <p>Start dragging one of the options (Text, Text Box, Upload, Group) into this box.</p>
					</div>

  			</div>

				<nav class="bottom">
					@if (isset($backUrl))
					<a class="" href="{{$backUrl}}">Back</a>
					@endif
					@if (isset($deleteUrl)) 
					<a class="" href="{{$deleteUrl}}" onclick="return confirm('Confirm delete?');" data-method='delete'>Delete</a>
					@endif

					<div class="submitGroup">
						<input type="submit" value="save" data-type-submit="save" class="saveSubmitButton" autocomplete="off">
					</div>
				</nav>
				
				@foreach ($groups as $group)
					<div class="panel panel-default infusePageSerialize" 
						data-serialize-tag="group" 
						data-serialize-page-value="1" 
						data-serialize-page-value-name="{{$group->name}}" 
						data-serialize-page-value-description="{{$group->description}}" 
						data-serialize-page-value-id="{{$group->id}}">

						<div class="panel-heading">
							<span class="groupEditActivateEvent">{{$group->name}}</span>
							<input style="display: none;" placeholder="enter title and return" class="form-control groupEditEvent" name="name" type="text">
						</div>
						<div class="panel-body">
							<p>{{$group->description}}</p>
							<div class="formBlock sortable">
							@foreach ($group->value as $element)

								@if ($element->type == "string")
									@include('infuse::page._string')
								@elseif ($element->type == "text")
									@include('infuse::page._text')
								@elseif ($element->type == "upload")
									@include('infuse::page._upload')
								@elseif ($element->type == "divider")
									@include('infuse::page._divider')
								@endif

							@endforeach

							</div>
						</div>
					</div>
				@endforeach

				<textarea class="hide pageData" name="pageData"></textarea>
				@if (isset($pri) && isset($pip))
					<input type="hidden" name="pri" value="{{$pri}}">
					<input type="hidden" name="pip" value="{{$pip}}">
					<input type="hidden" name="piNewId" value="" class="infuseSubPageCreateId">
				@elseif (isset($pip))
					<input type="hidden" name="pip" value="{{$pip}}">
				@endif

				@if ($method == "PUT")
				<input type="hidden" name="_method" value="{{$method}}">
				@endif
				
				{{-- Laravel csrf token --}}
				<input type="hidden" name="_token" value="{!! csrf_token() !!}" />
  		</form>

  	</div>

  </div>

</div> <!-- end of .container-fluid -->

<?php 
// Only send neccessary json to javascript
unset($pageInstance->pages); 
unset($pageInstance->pagesKeys); 
?>
<div class="hide">
	<div class="pageDataPassToJs" data-page-data='{!! json_encode($pageInstance) !!}'></div>
</div>

<script type="text/javascript">
$(document).ready(function() {
	window.InfusePages.init();
});
</script>


