<?php

class Jcp_Admin {
	public static $initiated = false;

	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}

	}

	public static function init_hooks() {

		self::$initiated = true;
		
		add_action( 'add_meta_boxes', array('Jcp_Admin', 'layers_child_add_meta_box'));
		add_action( 'save_post', array('Jcp_Admin', 'layers_child_save_meta_box_data'));
	}
	
	public static function layers_child_add_meta_box() {
 
 		$screens = array('page');
		foreach ( $screens as $screen ) {
		 	add_meta_box(
				'layers_child_meta_sectionid',
				'Multiple Links',
				array('Jcp_Admin', 'layers_child_meta_box_callback'),
				$screen,
					'normal',
					'high'
			   );
		}
	}
	
	public static function layers_child_meta_box_callback( $post ) {
	 
		wp_nonce_field( 'layers_child_meta_box', array('Jcp_Admin', 'layers_child_meta_box_nonce'));
	
		/*
		* Use get_post_meta() to retrieve an existing value
		* from the database and use the value for the form.
		*/
		$parametro = get_post_meta( $post->ID, 'parametro', true );
		$values = array('valor1' => "", 'valor2' => "", 'valor3' => "", 'valor4' => "", 'valor5' => "");
		for ($i = 1; $i <= 5; $i++) {
			$values['valor'.$i] = get_post_meta($post->ID, 'valor'.$i, true);
		}
		
		echo '<div id="dv_layers">';
		echo '<table id="original_layer" width=100%><tr>';
		echo '<th width=15%>Parameter</th><th>Value 1</th><th>Value 2</th><th>Value 3</th><th>Value 4</th><th>Value 5</th></tr>';
		
		if (@$parametro = unserialize($parametro)) {
			for ($i = 1; $i <= 5; $i++) {
				$values['valor'.$i] = unserialize($values['valor'.$i]);	
			}
		} else {
			$parametro = "";
		}
		
		for ($i = 0; $i < sizeof($parametro); $i++) {
			echo '<tr ' . ($i == 0 ? 'id="orig_tr"' : '').'>';
			echo '<td><input style="width:100%" type="text" name="parametro[]" value="'.$parametro[$i].'"/></td>';
			for ($n = 1; $n <= 5; $n++) {
				echo '<td><input style="width:100%" type="text" name="valor'.$n.'[]" value="'.$values['valor'.$n][$i].'"/></td>';
			}
			echo '</tr>';
		}
		
		echo '</tr></table></div>';
		
		echo '<a href="#" id="layers_add_more" style="margin-top:30px;display:block">Add more</a>';
		
		echo '<script>jQuery("#layers_add_more").click(function() { var cl = jQuery("#orig_tr").clone(); cl.find("input").val(""); jQuery("#original_layer").append(cl); return false; });</script>';
	}
	
	public static function layers_child_save_meta_box_data( $post_id ) {
		// Checks save status
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST[ 'layers_child_meta_box_nonce' ] ) && wp_verify_nonce( $_POST[ 'layers_child_meta_box' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
		
		if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
			return;
		}
		
		$skips = array();
		foreach (array("parametro", "valor1", "valor2", "valor3", "valor4", "valor5") as $key) {
			if (isset($_POST[$key]) && is_array($_POST[$key])) {
				$values = $_POST[$key];
				if ($key == "parametro") {
					$_values = array();
					foreach ($_POST[$key] as $k => $v) {
						if (trim($v) == '') {
							$skips[] = $k;
						} else {
							$_values[] = $v;
						}
					}
					$values = $_values;
				} else {
					$_values = array();
					foreach ($_POST[$key] as $k => $v) {
						if (!in_array($k, $skips)) {
							$_values[] = $v;
						}
					}
					$values = $_values;
				}
				
				update_post_meta($post_id, $key, serialize(array_map("sanitize_text_field", $values)));
			}
		}
	}
}
