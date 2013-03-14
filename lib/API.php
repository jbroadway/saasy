<?php

namespace saasy;

class API extends \Restful {
	public function get_members () {
		return App::org ()->members ();
	}
}

?>