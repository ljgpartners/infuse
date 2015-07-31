<?php namespace Bpez\Infuse;

/*
|--------------------------------------------------------------------------
| InfusePageBreadcrumb
|--------------------------------------------------------------------------
|
|
*/

class InfusePageBreadcrumb extends \SplDoublyLinkedList {


	function __construct(\Illuminate\Http\Request $request, \Illuminate\Session\SessionManager $session)
	{
		$this->session = $session;
		//$this->sessio->forget('infuse_pages_breadcrumbs');
		$this->request = $request;
		if ($this->session->has('infuse_pages_breadcrumbs')) {
			$this->unserialize($this->session->get('infuse_pages_breadcrumbs'));
		}
		$this->setIteratorMode(\SplDoublyLinkedList::IT_MODE_FIFO | \SplDoublyLinkedList::IT_MODE_KEEP);
	}

	function __destruct()
	{
		$this->session->put('infuse_pages_breadcrumbs', $this->serialize());
	}

	public function toArray()
	{
		$array = array();
		foreach ($this as $k => $v) {
		  $array[] = $v;
		}
		return $array;
	}

	public function reset()
	{
		$this->setIteratorMode(\SplDoublyLinkedList::IT_MODE_FIFO | \SplDoublyLinkedList::IT_MODE_DELETE);
		foreach ($this as $k => $v) {}
		$this->setIteratorMode(\SplDoublyLinkedList::IT_MODE_FIFO | \SplDoublyLinkedList::IT_MODE_KEEP);
	}

	public function rebuild($pip)
	{
		$nestedPageId = explode(";", $pip);
		$nestedPageId = array_pop($nestedPageId);

		$this->setIteratorMode(\SplDoublyLinkedList::IT_MODE_LIFO | \SplDoublyLinkedList::IT_MODE_KEEP);

		foreach ($this as $k => $v) {
			if ($v['page_instance'] == $nestedPageId) {
				$this->setIteratorMode(\SplDoublyLinkedList::IT_MODE_LIFO | \SplDoublyLinkedList::IT_MODE_DELETE);
			}
		}
		$this->setIteratorMode(\SplDoublyLinkedList::IT_MODE_FIFO | \SplDoublyLinkedList::IT_MODE_KEEP);
	}

	public function serialize()
	{
		return $this->toArray();
	}

	public function unserialize($data)
	{
		foreach ($data as $item) {
    	$this->push($item);
  	}
	}

	public function infusePageEdit(\InfusePage $infusePage, $pageInstance)
	{
		$breadcrumb = array(
			"page_root_id" => $infusePage->id,
			"page_instance" => "page",
			"page_instance_title" => $pageInstance->pageProperties->pageTitle
		);

		$this->reset();
		$this->push($breadcrumb);
	}

	public function infusePageNestedEdit(\InfusePage $infusePage, $pageInstance, $pageInstanceId)
	{
		$breadcrumb = array(
			"page_root_id" => $infusePage->id,
			"page_instance" => $pageInstanceId,
			"page_instance_title" => $pageInstance->pageProperties->pageTitle
		);

		$prevBreadcrumb = $this->top();

		if ($prevBreadcrumb['page_instance'] != $breadcrumb['page_instance']) {
			$this->push($breadcrumb);
		}
	}



}
