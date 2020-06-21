<?php
final class Loader {
    protected $registry;

	public function __construct($registry) {
                $dflag_show_data = 1;

//                echo "<!-- 2018-06-18 d1 - in class constructor setting protected registry pointer, -->\n";

		$this->registry = $registry;
	}
	
	public function controller($route, $data = array()) {

		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);
		
		$output = null;
		
		// Trigger the pre events
		$result = $this->registry->get('event')->trigger('controller/' . $route . '/before', array(&$route, &$data, &$output));
		
		if ($result) {
			return $result;
		}
		
		if (!$output) {
			$action = new Action($route);
			$output = $action->execute($this->registry, array(&$data));
		}
			
		// Trigger the post events
		$result = $this->registry->get('event')->trigger('controller/' . $route . '/after', array(&$route, &$data, &$output));
		
		if ($output instanceof Exception) {
			return false;
		}

		return $output;
	}
	
	public function model($route) {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);
		
		// Trigger the pre events
		$this->registry->get('event')->trigger('model/' . $route . '/before', array(&$route));


// 2017-06-25 - Ted debugging . . .

		if (!$this->registry->has('model_' . str_replace(array('/', '-', '.'), array('_', '', ''), $route)))
                {
			$file  = DIR_APPLICATION . 'model/' . $route . '.php';
			$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $route);
			
			if (is_file($file))
                        {
				include_once($file);
	
				$proxy = new Proxy();
				
				foreach (get_class_methods($class) as $method) {
					$proxy->{$method} = $this->callback($this->registry, $route . '/' . $method);
				}
				
				$this->registry->set('model_' . str_replace(array('/', '-', '.'), array('_', '', ''), (string)$route), $proxy);
			} else {
// 2017-06-25 - Ted debugging . . .
//                                echo "<br /> zztop - attempted to load model file using filename '$file',<br />\n";

				throw new \Exception('Error: Could not load model ' . $route . '!');
			}
		}
		
		// Trigger the post events
		$this->registry->get('event')->trigger('model/' . $route . '/after', array(&$route));
	}



	public function view($route, $data = array()) {
		$output = null;

                $dflag_show_data = 0;


if ( $dflag_show_data )
{
    echo "<!--\n";
    echo "2018-06-18 d2 - system/engine/loader.php, top of function view() \$data holds:";
//    echo "<pre>\n";
    print_r($data);
//    echo "</pre>\n";
    echo "-->\n";
}
		
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);


// echo "2018-06-18 d3 - system/engine/loader.php calling \$this->registry->get('event')->trigger(...) with a dynamic string and three arrays . . .<br />\n";

		// Trigger the pre events
		$result = $this->registry->get('event')->trigger('view/' . $route . '/before', array(&$route, &$data, &$output));
		
// if ( $dflag_show_data )
if ( 0 )
{
    echo "2018-06-18 d4 - system/engine/loader.php after call to trigger(...) \$data holds:";
    echo "<pre>\n";
    print_r($data);
    echo "</pre>\n";
}
		
		if ($result) {
			return $result;
		}
		
		if (!$output) {
			$template = new Template($this->registry->get('config')->get('template_type'));
			
			foreach ($data as $key => $value) {
				$template->set($key, $value);
// 2018-06-17 -

if ( 0 )
{
    if ( is_array($value) )
    {
        echo "<i> 2018-06-17 - system engine loader.php function view() copying ( $key => array_type_value ) pair to \$template,</i><br />\n";
    }
    else
    {
        echo "<i> 2018-06-17 - system engine loader.php function view() copying ( $key => $value ) pair to \$template,</i><br />\n";
    }

//     if ( $key == "products" )
//     if ( is_array($key) )
    if ( is_array($value) )
    {
        if ( 0 )
        {
            echo "<font color=green> + latest value is an array, showing contents of this array:<br />\n";
            echo "<pre>\n";
            print_r($value);
            echo "</pre></font>\n";
        }
        else
        {
            echo " + latest value is an array but not showing contents.<br />\n";
        }
    }
} // end local diagnostics

			}

// 2018-06-17 -
if ( 0 )
{
    echo "<i>2018-06-17 - system/engine/loader.php building reference to template file $route.tpl,</i><br />\n";
}
			$output = $template->render($route . '.tpl');
		}

// 2018-06-18 MON -
if ( 0 )
{
    echo "<font color=green> + showing contents of array \$data:<br /><pre>\n";
    print_r($data);
    echo "</pre></font>\n";
}

		// Trigger the post events
		$result = $this->registry->get('event')->trigger('view/' . $route . '/after', array(&$route, &$data, &$output));
		
		if ($result) {
			return $result;
		}
		
		return $output;
	}

	public function library($route) {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);
			
		$file = DIR_SYSTEM . 'library/' . $route . '.php';
		$class = str_replace('/', '\\', $route);

		if (is_file($file)) {
			include_once($file);

			$this->registry->set(basename($route), new $class($this->registry));
		} else {
			throw new \Exception('Error: Could not load library ' . $route . '!');
		}
	}
	
	public function helper($route) {
		$file = DIR_SYSTEM . 'helper/' . preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route) . '.php';

		if (is_file($file)) {
			include_once($file);
		} else {
			throw new \Exception('Error: Could not load helper ' . $route . '!');
		}
	}
	
	public function config($route) {
		$this->registry->get('event')->trigger('config/' . $route . '/before', array(&$route));
		
		$this->registry->get('config')->load($route);
		
		$this->registry->get('event')->trigger('config/' . $route . '/after', array(&$route));
	}

	public function language($route) {
		$output = null;
		
		$this->registry->get('event')->trigger('language/' . $route . '/before', array(&$route, &$output));
		
		$output = $this->registry->get('language')->load($route);
		
		$this->registry->get('event')->trigger('language/' . $route . '/after', array(&$route, &$output));
		
		return $output;
	}
	
	protected function callback($registry, $route) {
		return function($args) use($registry, &$route) {
			static $model = array(); 			
			
			$output = null;
			
			// Trigger the pre events
			$result = $registry->get('event')->trigger('model/' . $route . '/before', array(&$route, &$args, &$output));
			
			if ($result) {
				return $result;
			}
			
			// Store the model object
			if (!isset($model[$route])) {
				$file = DIR_APPLICATION . 'model/' .  substr($route, 0, strrpos($route, '/')) . '.php';
				$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', substr($route, 0, strrpos($route, '/')));

				if (is_file($file)) {
					include_once($file);
				
					$model[$route] = new $class($registry);
				} else {
					throw new \Exception('Error: Could not load model ' . substr($route, 0, strrpos($route, '/')) . '!');
				}
			}

			$method = substr($route, strrpos($route, '/') + 1);
			
			$callable = array($model[$route], $method);

			if (is_callable($callable)) {
				$output = call_user_func_array($callable, $args);
			} else {
				throw new \Exception('Error: Could not call model/' . $route . '!');
			}
			
			// Trigger the post events
			$result = $registry->get('event')->trigger('model/' . $route . '/after', array(&$route, &$args, &$output));
			
			if ($result) {
				return $result;
			}
						
			return $output;
		};
	}	
}
