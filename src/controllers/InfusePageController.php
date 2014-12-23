<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Bpez\Infuse\Util;
use Bpez\Infuse\InfusePageBreadcrumb;
use Bpez\Infuse\Referencer;

use Transit\Transit;
use Transit\Validator\ImageValidator;
use Transit\Transformer\Image\ResizeTransformer;
use Transit\File;

/*
|--------------------------------------------------------------------------
| InfusePageController 
|--------------------------------------------------------------------------
| Main logic for Infuse Page tool
|
*/

class InfusePageController extends BaseController {

	public $layout = 'infuse::layouts.application';

	public function __construct(\InfuseUser $user, \Illuminate\Http\Request $request, \Illuminate\Session\Store $session)
	{	
		$this->session = $session;
		$this->request = $request;
		$this->user = $user;
		View::share("user", $this->user);
		View::share("superAdmin", $this->user->is('Super Admin'));
		View::share('navigation', Config::get('infuse::navigation'));
		$rolePermission = (\Config::get("infuse::role_permission"))? true : false;
		View::share('rolePermission', $rolePermission);
		View::share('manageActive', true);
		View::share('databaseConnectionType', \Config::get('database.default'));

		$this->breadcrumbs = new InfusePageBreadcrumb($this->request, $this->session);

		if (Input::has("infuse_pages_section")) { 
			$infusePagesSection = Input::get("infuse_pages_section");
			Session::put('infuse_pages_section', $infusePagesSection);
			$this->breadcrumbs->reset();
		}

		
	}


	/**
	 * Redirect to first InfusePage or new page
	 * GET /admin/page
	 *
	 * @return Response
	 */
	public function index()
	{
		$infusePagesSection = Session::get('infuse_pages_section');
		
		try {
			$infusePage = InfusePage::select(DB::raw("id, title, page_data->'page' as page_data"))
				->where("navigation_section", "=", $infusePagesSection)
				->orderBy(DB::raw("display_order = 0, display_order"))
				->firstOrFail();
		} catch (ModelNotFoundException $e) {
			return Redirect::route('admin.page.create');
		}

		return Redirect::route('admin.page.edit', array("page" => $infusePage->id));
	}

	/**
	 * Show the form for creating a new resource.
	 * GET /admin/page/create
	 *
	 * @return Response
	 */
	public function create()
	{
		$this->layout->title = "Create Infuse Page | Infuse";
		$infusePagesSection = Session::get('infuse_pages_section');

		$resource = array();
		$resource['method'] = "POST";
		$nested = (Input::has("pri") && Input::has("pip"))? true : false;

		// Get all top level pages
		$resource['infusePages'] = InfusePage::where("navigation_section", "=", $infusePagesSection)
			->orderBy(DB::raw("display_order = 0, display_order"))
			->get();

		$resource['infusePage'] = new InfusePage;
		$path = Config::get('view.paths');
		$path = $path[0];
		$resource['infusePage']->page_data = \File::get(public_path().'/packages/bpez/infuse/page_template.json');


		if (!$nested) { 
			$resource['pageInstance'] = json_decode($resource['infusePage']->page_data);
			$resource['pageInstance'] = $resource['pageInstance']->page;
		} else { // nested
			$resource['pageInstance'] = json_decode($resource['infusePage']->page_data);
			$resource['pageInstance'] = $resource['pageInstance']->page;
			$resource['pri'] = Input::get("pri");
			$resource['pip'] = Input::get("pip");
		}

		$this->layout->content = View::make('infuse::page.create_edit', $resource);
	}

	/**
	 * Store a newly created resource in storage.
	 * POST /admin/page
	 *
	 * @return Response
	 */
	public function store()
	{
		$resource = array();
		$infusePagesSection = Session::get('infuse_pages_section');
		$nested = (Input::has("pri") && Input::has("pip"))? true : false;
		
		// Update page
		if (Input::has("pageData")) { 
			$updatePageInstance = Input::get("pageData");
			$updatePageInstance = json_decode($updatePageInstance);

			if (!$nested) {

				$infusePage = new InfusePage;
				$infusePage->navigation_section = $infusePagesSection;
				$path = Config::get('view.paths');
				$path = $path[0];
				$infusePage->page_data = \File::get(public_path().'/packages/bpez/infuse/page_template.json');
				$fullJsonColumn = json_decode($infusePage->page_data);

				$infusePage->title = $updatePageInstance->pageProperties->pageTitle;
				$fullJsonColumn->page->pageProperties = $updatePageInstance->pageProperties;
				$fullJsonColumn->page->pageValues = $updatePageInstance->pageValues;

				// Update whole json column
				$infusePage->page_data = json_encode($fullJsonColumn);
				$infusePage->save();

				Util::flash(array(
					"message" => "Created page.", 
					"type" => "success"
					)
				);

			} else {
				// do neested
			 	$pri = Input::get("pri"); // page root id
				$pip = Input::get("pip"); // page instance path
				$piNewId = Input::get("piNewId"); // page instance id
				$newPip = $pip.";".$piNewId; // New path to redirect to

				$psqlSelectOnlyNestedInstance = "page_data";
				foreach (explode(";", $pip) as $p) {
					$psqlSelectOnlyNestedInstance .= "->'{$p}'->'pages'";
				}
				$psqlSelectOnlyNestedInstance = preg_replace('/->\'pages\'$/', '', $psqlSelectOnlyNestedInstance);
				$psqlSelectOnlyNestedInstance .= " as page_instance ";

				
				try {
					$infusePage = InfusePage::select(DB::raw("id, title, page_data, {$psqlSelectOnlyNestedInstance}"))
						->where("navigation_section", "=", $infusePagesSection)
						->where("id", "=", $pri)
						->firstOrFail();
					$fullJsonColumn = $infusePage->page_data;
					$pageInstanceJson = $infusePage->page_instance;
				} catch (ModelNotFoundException $e) {
					Util::flash(array(
						"message" => "Failed to create page.", 
						"type" => "error"
						)
					);
					return Redirect::route('admin.page.index');
				}

				$path = Config::get('view.paths');
				$path = $path[0];
				$infusePage->page_data = \File::get(public_path().'/packages/bpez/infuse/page_template.json');
				$newPageTemplate = json_decode($infusePage->page_data);
				$newPageTemplate = $newPageTemplate->page;

				$newPageTemplate->pageProperties = $updatePageInstance->pageProperties;
				$newPageTemplate->pageValues = $updatePageInstance->pageValues;

				//  Update page instance
				$fullJsonColumnDecoded = json_decode($fullJsonColumn);
				
				$updatePath = "";
				$pip = Input::get("pip"); // page instance path
				$parent = explode(";", $pip);
				foreach ($parent as $p) {
					$updatePath .= "{$p}->pages->";
				}
				$updatePath = preg_replace('/->pages->$/', '', $updatePath);


				$reference =& Referencer::getReference($updatePath, $fullJsonColumnDecoded);
				$reference->pages->{$piNewId} = $newPageTemplate;
				unset($newPageTemplate);
				array_push($reference->pagesKeys, $piNewId);

				$fullJsonColumnEncoded = json_encode($fullJsonColumnDecoded);
				unset($fullJsonColumnDecoded);

				// Update whole json column
				$infusePage->page_data = $fullJsonColumnEncoded;
				$infusePage->save();

				Util::flash(array(
					"message" => "Created page.", 
					"type" => "success"
					)
				);

				return Redirect::route('admin.page.edit', array("page" => $infusePage->id, 'pri' => $pri, 'pip' => $newPip));
			}
			

			return Redirect::route('admin.page.edit', array("page" => $infusePage->id));

		}

		Util::flash(array(
			"message" => "Failed to create page.", 
			"type" => "error"
			)
		);

		return Redirect::route('admin.page.index');
	}

	/**
	 * Display the specified resource.
	 * GET /admin/page/{page} 
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		return Redirect::route('admin.page.edit', array("page" => $id));
	}

	/**
	 * Show the form for editing the specified resource.
	 * GET /admin/page/{page}/edit
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{	
		$this->layout->title = "Edit Infuse Page | Infuse"; 
		$infusePagesSection = Session::get('infuse_pages_section');
		$nested = (Input::has("pip"))? true : false;

		if (Input::has("pop")) {
			$this->breadcrumbs->pop();
		}

		if (Input::has("rpip") && $nested) {
			$this->breadcrumbs->rebuild(Input::get("pip"));
		}
		
		$resource = array();
		$resource['method'] = "PUT";

		// Get all top level pages
		$resource['infusePages'] = InfusePage::where("navigation_section", "=", $infusePagesSection)
			->orderBy(DB::raw("display_order = 0, display_order"))
			->get();


		if (!$nested) { 
			try {
				$resource['infusePage'] = InfusePage::select(DB::raw("id, title, page_data->'page'  as page_data"))
					->where("navigation_section", "=", $infusePagesSection)
					->where("id", "=", $id)
					->firstOrFail();

				$resource['pageInstance'] = json_decode($resource['infusePage']->page_data);

				$this->breadcrumbs->infusePageEdit($resource['infusePage'], $resource['pageInstance']);

			} catch (ModelNotFoundException $e) {
				return Redirect::route('admin.page.index');
			}

			$resource['deleteUrl'] = URL::to("admin/page/{$resource['infusePage']->id}");
			$resource['backUrl'] = URL::route('dashboard');

		} else { // nested
			$pip = Input::get("pip");
			$resource['pip'] = $pip;
			$pageInstanceId = "";

			$psqlSelectOnlyNestedInstance = "page_data";
			foreach (explode(";", $pip) as $p) {
				$psqlSelectOnlyNestedInstance .= "->'{$p}'->'pages'";
				$pageInstanceId = $p;
			}
			$psqlSelectOnlyNestedInstance = preg_replace('/->\'pages\'$/', '', $psqlSelectOnlyNestedInstance);
			$psqlSelectOnlyNestedInstance .= " as page_instance ";

			$resource['infusePage'] = InfusePage::select(DB::raw("id, title, {$psqlSelectOnlyNestedInstance}"))
				->where("navigation_section", "=", $infusePagesSection)
				->where("id", "=", $id)->firstOrFail();

			$resource['pageInstance'] = json_decode($resource['infusePage']->page_instance);
			
			$this->breadcrumbs->infusePageNestedEdit($resource['infusePage'], $resource['pageInstance'], $pageInstanceId);

			$query = ($pip == "page")? "" : "?pip={$pip}";
			$resource['deleteUrl'] = URL::to("admin/page/{$resource['infusePage']->id}{$query}");

			$pip = implode(';', explode(';', $pip, -1));
			$query = ($pip == "page")? "" : "?pip={$pip}&pop=1";
			$resource['backUrl'] = URL::to("admin/page/{$resource['infusePage']->id}/edit{$query}");

		}

		$resource['breadcrumbs'] = $this->breadcrumbs;

		$this->layout->content = View::make('infuse::page.create_edit', $resource);
	}

	/**
	 * Update the specified resource in storage.
	 * PUT /admin/page/{page} 
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$resource = array();
		$infusePagesSection = Session::get('infuse_pages_section');
		$nested = (Input::has("pip"))? true : false;
		
		// Update page
		if (Input::has("pageData")) {
			$updatePageInstance = Input::get("pageData");
			$updatePageInstance = json_decode($updatePageInstance);

			$jsonQuery = "page_data";
			if ($nested) {
				$pip = Input::get("pip"); // page instance path
				$parent = explode(";", $pip);
				foreach ($parent as $p) {
					$jsonQuery .= "->'{$p}'->'pages'";
				}
				$jsonQuery = preg_replace('/->\'pages\'$/', '', $jsonQuery);

			} else {
				$rootPage = true;
				$jsonQuery .= "->'page'";
			}

			$select = "{$jsonQuery} as page_instance, {$jsonQuery}->'pageValues' as page_values, {$jsonQuery}->'pageProperties' as page_properties";
			

			try {
				$infusePage =  InfusePage::select(DB::raw("id, title, page_data, {$select}"))
					->where("navigation_section", "=", $infusePagesSection)
					->where("id", "=", $id)
					->firstOrFail();
			} catch (ModelNotFoundException $e) {
				Util::flash(array(
					"message" => "Can not update this page.", 
					"type" => "error"
					)
				);
				return Redirect::route('admin.page.index');
			}


			// Copy original page instance json
			$pageInstance = json_decode($infusePage->page_instance);

			//  Update page properties of page instance
			$pageInstance->pageProperties  = $updatePageInstance->pageProperties;
			if (isset($rootPage)) {
				$infusePage->title = $updatePageInstance->pageProperties->pageTitle;
			}
			
			//  Update page values of page instance
			
			// Process adding new images
			$newImages = array();
			foreach ($updatePageInstance->pageValues as &$item) {
				if ($item->type == "upload") {
					$newImages[$item->id] = false;

					if (isset($_FILES["{$item->id}"])) {
						$transit = new Transit($_FILES["{$item->id}"]);

						$transit->setDirectory($infusePage->uploadPath("page_data"));
						
						try { 

							$success = $transit->upload();
							
							if ($success) {
								$fileName = explode(DIRECTORY_SEPARATOR, $transit->getOriginalFile());
			          $fileName = end($fileName);
								
			          if(strpos($fileName, "@2x.") !== FALSE) {
			            $uploadPath = $infusePage->uploadPath("page_data");
			            $halfRetinaSize = floor($transit->getOriginalFile()->width()/2);
			            $retinaFileName = $fileName;

			            $fileName = explode("@2x.", $fileName);
			            $fileName = $fileName[0].".".$fileName[1];
			            if (copy($uploadPath.$retinaFileName, $uploadPath.$fileName)) {

			              $transitRetina = new ResizeTransformer(array('width' => $halfRetinaSize));

			              if (!$transitRetina->transform(new File($uploadPath.$fileName), true)) {
			                throw new Exception("Failed to resize retina for non retina version.");
			              }
			            } else {
			              throw new Exception("Failed to copy retina image for processing.");
			            }
			          }

			          $item->value = $fileName;
			          $newImages[$item->id] = true;

							} 
						} catch (Exception $e) {
							$fileErrors["{$item->id}"] = $e->getMessage();
						}
					} // end of isset($_FILES["{$item->id}"])
				} // end of $item->type == "upload"
			} // end of foreach

			
			// check for images to remove
			foreach ($pageInstance->pageValues as &$item) {

				// Checks if upload && replacement image uploaded then removes old file
				if (($item->type == "upload" && isset($newImages[$item->id]) && $newImages[$item->id] == true) || 
						($item->type == "upload" && !isset($newImages[$item->id]))) {

					$uploadedFile = $infusePage->uploadPath("page_data").$item->value;
					if (!empty($item->value) && file_exists($uploadedFile)) {
						unlink($uploadedFile);
					}
				}
			}


			$pageInstance->pageValues = $updatePageInstance->pageValues; 
		
			//  Update page instance
			$fullJsonColumnDecoded = json_decode($infusePage->page_data);
			$updatePath = "";
			if ($nested) {
				$pip = Input::get("pip"); // page instance path
				$parent = explode(";", $pip);
				foreach ($parent as $p) {
					$updatePath .= "{$p}->pages->";
				}
				$updatePath = preg_replace('/->pages->$/', '', $updatePath);

			} else {
				$updatePath .= "->page";
			}

			$reference =& Referencer::getReference($updatePath, $fullJsonColumnDecoded);
			$reference = $pageInstance;

			$fullJsonColumnEncoded = json_encode($fullJsonColumnDecoded);
			unset($fullJsonColumnDecoded);

			// Update whole json column
			$infusePage->page_data = $fullJsonColumnEncoded;
			$infusePage->save();

			Util::flash(array(
				"message" => "Saved page.", 
				"type" => "success"
				)
			);

			if ($nested) {
				$pip = Input::get("pip"); 
				return Redirect::to("admin/page/{$infusePage->id}/edit?pip={$pip}");
			} else {
				return Redirect::route('admin.page.edit', array("page" => $infusePage->id));
			}


		}

		Util::flash(array(
			"message" => "Can not update this page.", 
			"type" => "error"
			)
		);

		return Redirect::route('admin.page.index');
	}

	/**
	 * Remove the specified resource from storage.
	 * DELETE /admin/page/{page} 
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$resource = array();
		$infusePagesSection = Session::get('infuse_pages_section');
		$nested = (Input::has("pip"))? true : false;


		if (!$nested) {

			$infusePage =  InfusePage::where("navigation_section", "=", $infusePagesSection)
					->where("id", "=", $id)
					->firstOrFail();
			if ($infusePage->delete()) {
				Util::flash(array(
					"message" => "Deleted page.", 
					"type" => "success"
					)
				);
			}

			

			return Redirect::route('admin.page.index');

		} else {
			// do neested
			$pip = Input::get("pip"); // page instance path

			$parent = explode(";", $pip);
			$nestedPageId = array_pop($parent);

			$psqlSelectOnlyNestedInstance = "page_data";
			foreach ($parent as $p) {
				$psqlSelectOnlyNestedInstance .= "->'{$p}'->'pages'";
			}
			$psqlSelectOnlyNestedInstance = preg_replace('/->\'pages\'$/', '', $psqlSelectOnlyNestedInstance);
			$psqlSelectOnlyNestedInstance .= " as parent_instance ";

			
			try {
				$infusePage = InfusePage::select(DB::raw("id, title, page_data, {$psqlSelectOnlyNestedInstance}"))
					->where("navigation_section", "=", $infusePagesSection)
					->where("id", "=", $id)
					->firstOrFail();
				$fullJsonColumn = $infusePage->page_data;
				$pageParentInstanceJson = $infusePage->parent_instance;
			} catch (ModelNotFoundException $e) {
				return Redirect::route('admin.page.index');
			}

			$pageParentInstanceJsonDecoded = json_decode($pageParentInstanceJson);

			// check for removing images & nested pages iamges recurisively
			$tempPage = $pageParentInstanceJsonDecoded->pages->{$nestedPageId};


	    function recursiveDeleteOfNestedFiles($page, $infusePage)
	    {
			    foreach($page->pagesKeys as $key){
			    	 recursiveDeleteOfNestedFiles($page->pages->{$key}, $infusePage);
			    }
			    //echo $page->pageProperties->pageTitle."</br>";
			    foreach ($page->pageValues as $value) {
			    	if ($value->type == "upload" &&  !empty($value->value))	{
			    		//echo "delete: ".$value->value."</br>";
			    		$uploadedFile = $infusePage->uploadPath("page_data").$value->value;
							if (!empty($value->value) && file_exists($uploadedFile)) {
								unlink($uploadedFile);

			          $name = pathinfo($uploadedFile, PATHINFO_FILENAME);
			          $ext  = pathinfo($uploadedFile, PATHINFO_EXTENSION);
			          $retinaImage = $infusePage->uploadPath("page_data").$name."@2x.".$ext;
			          if (file_exists($retinaImage)) {
			            unlink($retinaImage);
			          }
							}
			    	}
			    }
			}

	    recursiveDeleteOfNestedFiles($tempPage, $infusePage);
	    

			// Remove nested page
			unset($pageParentInstanceJsonDecoded->pages->{$nestedPageId});
			// Remove reference from pagesKeys
			$key = array_search($nestedPageId, $pageParentInstanceJsonDecoded->pagesKeys);
			if ($key !== false) {
				unset($pageParentInstanceJsonDecoded->pagesKeys[$key]);
			}
			
			
			//  Update page instance
			$fullJsonColumnDecoded = json_decode($fullJsonColumn);
			$updatePath = "";
			foreach ($parent as $p) {
				$updatePath .= "{$p}->pages->";
			}
			$updatePath = preg_replace('/->pages->$/', '', $updatePath);

			$reference =& Referencer::getReference($updatePath, $fullJsonColumnDecoded);
			$reference = $pageParentInstanceJsonDecoded;
			unset($pageParentInstanceJsonDecoded);
			
			$fullJsonColumnEncoded = json_encode($fullJsonColumnDecoded);
			unset($fullJsonColumnDecoded);

			// Update whole json column
			$infusePage->page_data = $fullJsonColumnEncoded;
			$infusePage->save();

			$this->breadcrumbs->pop();

			Util::flash(array(
				"message" => "Deleted page.", 
				"type" => "success"
				)
			);

			if ($parent != "page") {
				$parent = implode(";", $parent);
				return Redirect::route('admin.page.edit', array("page" => $infusePage->id, 'pri' => $id, 'pip' => $parent));
			} else {
				return Redirect::route('admin.page.edit', array("page" => $infusePage->id));
			}
		}


		Util::flash(array(
			"message" => "Failed to delete page.", 
			"type" => "error"
			)
		);
	}


	


}