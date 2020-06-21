<?php
class ControllerCommonSearch extends Controller {
	public function index() {
		$this->load->language('common/search');

		$data['text_search'] = $this->language->get('text_search');

		if (isset($this->request->get['search'])) {
			$data['search'] = $this->request->get['search'];
		} else {
			$data['search'] = '';
		}

if ( 0 )
{
    echo "2018-06-18 - catalog/controller/common/search.php just set \$data to:<br />\n";
    {
        echo "<pre>\n";
        print_r($data);
        echo "</pre>\n";
    }
    echo "2018-06-18 + catalog/controller/common/search.php now calling \$this->load->view('common/search', \$data) . . .<br />\n";
}

		return $this->load->view('common/search', $data);
	}
}
