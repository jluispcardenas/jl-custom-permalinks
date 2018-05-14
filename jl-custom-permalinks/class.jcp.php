<?php

class Jcp {
	public static $initiated = false;
	public static $valores = array();

	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}
	
	function plugin_activation() {
	}
	
	function plugin_deactivation() {
	}
	
	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() {
		global $wpdb, $wp_rewrite;
		
		self::$initiated = true;
		
		if(isset($_SERVER["REQUEST_URI"])) {
			$uri = trim($_SERVER['REQUEST_URI']);
			$uri = preg_replace("#\/$#", "", $uri);
			$parts = explode("/", $uri);
			$path = $parts[sizeof($parts)-1];
			if (strlen($path) > 3) {
				$res = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key='parametro' AND meta_value LIKE '%" . $path . "%'");
				if ($res && !empty($res)) {
					$values = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}postmeta WHERE post_id = " . $res[0]->post_id . " 
						AND meta_key IN ('parametro','valor1','valor2','valor3','valor4','valor5')");
					$valor1 = $valor2 = $valor3 = $valor4 = $valor5 = "";
					$index = 0;
					foreach ($values as $k => $v) {
						$des = unserialize(unserialize((string)$v->meta_value));
						if ($v->meta_key == 'parametro') {
							for($i = 0; $i < sizeof($des); $i++) {
								if (stripos($des[$i], $path) !== FALSE) {
									$index = $i;
									break;
								}
							}
						} else {
							$new_key = $v->meta_key;
							$$new_key = $des[$index];
						}
					}
					if (!empty($valor1)) {
						self::$valores = compact('valor1', 'valor2', 'valor3', 'valor4', 'valor5');
						
						self::add_filters();

						add_rewrite_rule('^' . $path . '\/?$', 'index.php?page_id=' . $res[0]->post_id, 'top');
						$wp_rewrite->flush_rules(false);
						add_filter('redirect_canonical', '__return_false');
					}
					
				} else {
					self::add_filters();
				}
			}
		}
	}
	  
	public static function replace_valor_content($content)
	{
		if (isset(self::$valores['valor1'])) {
			for ($i = 1; $i <= 5; $i++) {
				$content = preg_replace('#\[value'.$i.'([^\]]*?)\]#i', self::$valores['valor'.$i], $content);
			}
		} else {
			for ($i = 1; $i <= 5; $i++) {
				$content = preg_replace('#\[value'.$i.':([^\]]*?)\]#i', '\\1', $content);
			}
		}
		return $content;
	}
	
	private static function add_filters() {
		add_filter('single_post_title', array('Jcp', 'replace_valor_content'));
		add_filter('the_title', array('Jcp', 'replace_valor_content'));
		add_filter('the_content', array('Jcp', 'replace_valor_content'), 11);
		add_filter('wpseo_title', array('Jcp', 'replace_valor_content'));
		add_filter('wpseo_metadesc', array('Jcp', 'replace_valor_content'), 10, 1);
		add_filter('wpseo_canonical', array('Jcp', 'replace_valor_content'), 10, 1);	
	}
}
