<?php 
add_action( 'wp_enqueue_scripts', 'salient_child_enqueue_styles');
function salient_child_enqueue_styles() {
	
		$nectar_theme_version = nectar_get_theme_version();
		
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css', array('font-awesome'), $nectar_theme_version);

    if ( is_rtl() ) 
   		wp_enqueue_style(  'salient-rtl',  get_template_directory_uri(). '/rtl.css', array(), '1', 'screen' );
}
add_action( 'admin_menu', 'linked_url' );
function linked_url() {
    add_menu_page( 'Order', 'Order', 'read', 'custom-order', '', 'dashicons-text',2  );
    add_menu_page( 'Invoices', 'Invoices', 'read', 'custom-quotes', '', 'dashicons-text',2  );
    add_menu_page( 'Quotes', 'Quotes', 'read', 'custom-quotes', '', 'dashicons-text',2  );
    add_menu_page( 'Products', 'Products', 'read', 'custom-products', '', 'dashicons-text',2  );
}

add_action( 'admin_menu' , 'linkedurl_function' );
function linkedurl_function() {
    global $menu;
    //echo '<pre>';print_r($menu);echo '</pre>';
    $menu['2.84428'] = $menu['55.5'];
    unset($menu['55.5']);
    $menu['2.0264'] = $menu['27'];
    unset($menu['27']);
    $menu['2.24048'] = $menu['26'];
    unset($menu['26']);
    $menu['2.2202'] = $menu['28'];
    unset($menu['28']);
    //$menu['2.84428'][2] = admin_url().'/edit.php?post_type=shop_order';
}

function my_account_menu_items( $items ) {
    $logout = $items['customer-logout'];
    $subscriptions = $items['subscriptions'];
    $downloads = $items['downloads'];
    $editaddress = $items['edit-address'];
    $paymentmethods = $items['payment-methods'];
    $editaccount = $items['edit-account'];
    
    unset( $items['subscriptions'] );
    unset( $items['customer-logout'] );
    unset( $items['downloads'] );
    unset( $items['edit-address'] );
    unset( $items['payment-methods'] );
    unset( $items['edit-account'] );
    
	$items['quotes-invoices'] = __( 'Quotes & Invoices', 'woocommerce' );
	$items['subscriptions'] = $subscriptions;
	$items['downloads'] = $downloads;
	$items['edit-address'] = $editaddress;
	$items['payment-methods'] = 'Payment Methods';
	$items['edit-account'] = $editaccount;
	$items['gdpr-assistant'] = __( 'GDPR Assistant', 'woocommerce' );
	$items['contract-reports'] = __( 'Contract Reports', 'woocommerce' );
	$items['my-files'] = __( 'My Saved Files', 'woocommerce' );
	if( current_user_can('administrator') ) {
	    $items['internal-database'] = __( 'Database', 'woocommerce' );
	}
	$items['time-clock'] = 'Time Clock';
	$items['customer-logout'] = $logout;
	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'my_account_menu_items' );

function add_my_account_endpoint() {
    add_rewrite_endpoint( 'gdpr-assistant', EP_PAGES );
    add_rewrite_endpoint( 'quotes-invoices', EP_PAGES );
    add_rewrite_endpoint( 'internal-database', EP_PAGES );
    add_rewrite_endpoint( 'contract-reports', EP_PAGES );
    add_rewrite_endpoint( 'my-files', EP_PAGES );
    add_rewrite_endpoint( 'time-clock', EP_PAGES );
}
add_action( 'init', 'add_my_account_endpoint' );

function gdpr_endpoint_content() {
	$uid = get_current_user_id();
    $gf_resume_url = get_user_meta($uid,'gf_resume_url',true);
	$args = array(
	  'numberposts' => 1,
	  'post_type'   => 'general_information',
	  'post_status'   => 'publish',
	  'author'   => $uid
	);
	 
	$posts = get_posts( $args );
	?>
	<div class="gdpr-section">
	    <img src="<?php echo site_url(); ?>/wp-content/uploads/2019/02/logoimg.png">
	    <hr><pre align="center"><b>GDPR General Company Information Questionnaire</b></pre><hr>
	    <hr style="border-top: dotted 1px; margin: 30px 0px 0px;">
	    <br>
		<?php if( !empty($posts) ){
			echo do_shortcode('[gravityform id="2" title=false description=false ajax=true]');
		}else{ ?>
			<a href="<?php echo get_permalink(); ?>gdpr-assistant?app=new" class="btn-gf">Start The Process</a> 
			<?php if( $_GET['gf_token']=='' && $gf_resume_url!=''  ){ ?>
				<a href="<?php echo $gf_resume_url; ?>" class="btn-gf">Previously Started Application</a>
			<?php } ?>
			<?php if( $_GET['app']=='new' ){ ?>
			<?php echo do_shortcode('[gravityform id="2" title=false description=false ajax=true]'); ?>
			<?php }
		}		?>
	</div>
	<?php
}
add_action( 'woocommerce_account_gdpr-assistant_endpoint', 'gdpr_endpoint_content' );

function quotes_invoices_content() {
	echo do_shortcode('[sliced-client-area]');
}
add_action( 'woocommerce_account_quotes-invoices_endpoint', 'quotes_invoices_content' );

function internal_database_content() {
    echo '<a href="https://www.mcs.support" class="button button-primary" target="_blank">Launch Database</a>';
}
add_action( 'woocommerce_account_internal-database_endpoint', 'internal_database_content' );

function contract_reports_content() {
    $uid = get_current_user_id();
    $contract_reports = get_user_meta($uid,'contract_reports');
    //echo '<pre>';print_r($contract_reports);echo '</pre>';
    echo '<h3>Contract Reports</h3>';
    echo '<table class="woocommerce-MyAccount-paymentMethods shop_table account-payment-methods-table">
		<thead>
			<tr>
			<th width="70px">File</th>
			<th>Name</th>
			<th width="100px">Download</th>
			</tr>
		</thead>
		<tbody>';
    foreach( $contract_reports as $cs ){
        echo '<tr>';
        if( $cs['post_mime_type'] == 'application/pdf' ){
            echo '<td><i class="fa fa-file-pdf-o" style="color:red;font-size: 24px;"></i></td>';
        }else if( $cs['post_mime_type'] == 'application/msword' || $cs['post_mime_type'] == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ){
            echo '<td><i class="fa fa-file-word-o" style="color:blue;font-size: 24px;"></i></td>';
        }else echo '<td><i class="fa fa-file" style="color:cyan;font-size: 24px;"></i></td>';
        echo '<td><span class="file-nm">'.$cs['post_title'].'</span></td>';
        echo '<td><a href="'.$cs['guid'].'" class="button button-primary" target="_blank">Download</a></td>';
        echo '</tr>';
    }
     echo '</tbody>'; 
    echo '</table>'; 
}
add_action( 'woocommerce_account_contract-reports_endpoint', 'contract_reports_content' );

function my_files_content() {
    $uid = get_current_user_id();
    $my_files = get_user_meta($uid,'my_files');
    $upload_dir = wp_upload_dir();
    //echo '<pre>';print_r($my_files);echo '</pre>';
    echo '<form action="https://www.mcs.support/wp-content/themes/salient-child/my-file-download.php" method="post">';
    echo '<h3>Upload Files</h3>';
    echo '<input type="hidden" name="path" value="'.$upload_dir["basedir"].'">';
    echo '<table class="woocommerce-MyAccount-paymentMethods shop_table account-payment-methods-table">
		<thead>
			<tr>
			<th width="30px"><input type="checkbox" class="my-file-ck" id="checkAll"></th>
			<th width="70px">File</th>
			<th>Name</th>
			</tr>
		</thead>
		<tbody>';
    foreach( $my_files as $cs ){
        echo '<tr>';
        echo '<td><input type="checkbox" class="my-file-ck" name="my_files[]" value="'.get_attached_file($cs['ID']).'"></td>';
        if( $cs['post_mime_type'] == 'application/pdf' ){
            echo '<td><i class="fa fa-file-pdf-o" style="color:red;font-size: 24px;"></i></td>';
        }else if( $cs['post_mime_type'] == 'application/msword' || $cs['post_mime_type'] == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ){
            echo '<td><i class="fa fa-file-word-o" style="color:blue;font-size: 24px;"></i></td>';
        }else echo '<td><i class="fa fa-file" style="color:cyan;font-size: 24px;"></i></td>';
        echo '<td><span class="file-nm">'.$cs['post_title'].'</span></td>';
        //echo '&nbsp;<a href="'.$cs['guid'].'" class="button button-primary" target="_blank">Download</a>';
        echo '</tr>';
    } 
    echo '</tbody>'; 
    echo '</table>';
    echo '<input type="submit" class="button button-primary downloads" value="Downloads Selected Files" disabled>';
    echo '</form>'; 
    echo '<br><h3>Saved Files</h3>';
    $user_ID = get_current_user_id();
    $user_pod = pods ('user', $user_ID);
    $fields = array( 'my_files' );
    echo '<div class="my-files-form">';
    echo $user_pod->form( $fields );
    echo '</div>';
    echo '<script>';
    echo"jQuery(document).ready(function(){
    jQuery('#checkAll').click(function(){
        jQuery('input:checkbox').not(this).prop('checked', this.checked);
    });
    jQuery('.my-file-ck').change(function() {
        var n = jQuery('.my-file-ck:checked:checked').length;
        console.log(n);
        if(n == 0) {
            jQuery('.downloads').attr('disabled', 'disabled');
        } else {
            jQuery('.downloads').removeAttr('disabled');
        }
    });
    });";
    echo '</script>';
}
add_action( 'woocommerce_account_my-files_endpoint', 'my_files_content' );

function time_clock_content() {
	echo do_shortcode('[show_aio_time_clock]');
}
add_action( 'woocommerce_account_time-clock_endpoint', 'time_clock_content' );

//add_action('admin_init', 'allow_contributor_uploads');
function allow_contributor_uploads() {
    $customer = get_role('customer');
    $customer->add_cap('upload_files');
}

add_filter( 'gform_save_and_continue_resume_url', function( $resume_url, $form, $token, $email ) {
    if( $form['id']=2  ){
        $uid = get_current_user_id();
        update_user_meta($uid,'gf_resume_url',$resume_url);
        //echo '<pre>';print_r($resume_url);echo '</pre>';
    }
    $resume_url = add_query_arg( array( 'gf_token' => $token ), array_shift( explode( '?', $resume_url ) ) );
 
    return $resume_url;
}, 10, 4 );

add_action( 'gform_after_submission', 'access_entry_via_field', 10, 2 );
function access_entry_via_field( $entry, $form ) {
    $uid = get_current_user_id();
	global $current_user,$wpdb;
	$table = $wpdb->posts;
	$display_name = $current_user->data->display_name;
	if( $form['id']==2  ){
		$args = array(
		  'numberposts' => 1,
		  'post_type'   => 'general_information',
		  'post_status'   => 'publish',
		  'author'   => $uid
		);
		 
		$posts = get_posts( $args );
		
		if( !empty($posts) ){
			$post_id = $posts[0]->ID;
			$old_id = get_post_meta($post_id,'entry_id',true);
			GFAPI::delete_entry( $old_id );
			update_post_meta($post_id,'entry_id',$entry['id']);
			update_post_meta($post_id,'entry_arr',$entry);
			update_post_meta($post_id,'entry_user',$uid);
		}else{
			$posttitle = $display_name.' - General Information';		
			$wpdb->insert($table, array(
				'post_title' => $posttitle,
				'post_name' => sanitize_title_with_dashes($posttitle),
				'post_status' => 'publish',
				'post_date' => date('Y-m-d H:i:s'),
				'post_author' => $uid,
				'post_type' => 'general_information'
			));
			$lastid = $wpdb->insert_id;
			update_post_meta($lastid,'entry_id',$entry['id']);
			update_post_meta($lastid,'entry_arr',$entry);
			update_post_meta($lastid,'entry_user',$uid);
		}
	}else if( $form['id']==5 || $form['id']==6 || $form['id']==9 || $form['id']==11 || $form['id']==14 || $form['id']==16 || $form['id']==17 ){
	    //echo '<pre>';print_r($_POST);echo '</pre>';exit;
	    if( $form['id']==5 ){
	        $slug = 'website-privacy-policy-basic-no-data';
	        $pname = 'Website Privacy Policy Basic No Data';
	    }else if( $form['id']==6 ){
	        $slug = 'website-privacy-policy-basic-no-cookies';
	        $pname = 'Website Privacy Policy Basic No Cookies';
	    }else if( $form['id']==9 ){
	        $slug = 'website-privacy-policy-first-party-cookies';
	        $pname = 'Website Privacy Policy First Party Cookies';
	    }else if( $form['id']==11 ){
	        $slug = 'website-privacy-policy-first-party-cookies-and-analytics';
	        $pname = 'Website Privacy Policy First Party Cookies and Analytics';
	    }else if( $form['id']==14 ){
	        $slug = 'website-privacy-policy-first-and-third-party-cookies';
	        $pname = 'Website Privacy Policy First and Third Party Cookies';
	    }else if( $form['id']==16 ){
	        $slug = 'website-privacy-policy-first-and-third-party-cookies-and-analytics';
	        $pname = 'Website Privacy Policy First and Third Party Cookies and Analytics';
	    }else if( $form['id']==17 ){
	        $slug = 'job-applicant-privacy-notice';
	        $pname = 'Job Applicant Privacy Notice';
	    }
	    foreach( $_POST as $k=>$p ) {
    	    if( strpos( $k, 'label_' ) !== false) {
                $entry[$k] = $p;
                gform_update_meta( $entry['id'], $k, $p );
            }
	    }
	    
	    $args = array(
		  'numberposts' => 1,
		  'post_type'   => 'questionnaire',
		  'post_status'   => 'publish',
		  'author'   => $uid,
		  'tax_query' => array(
		        array(
                    'taxonomy' => 'question-category',
                    'terms' => $slug,
                    'field' => 'slug'
                )
            )
		);
		 
		$posts = get_posts( $args );
		if( !empty($posts) ){
			$post_id = $posts[0]->ID;
			$old_id = get_post_meta($post_id,'entry_id',true);
			GFAPI::delete_entry( $old_id );
			update_post_meta($post_id,'entry_id',$entry['id']);
			update_post_meta($post_id,'entry_arr',$entry);
			update_post_meta($post_id,'entry_user',$uid);
		}else{
			$posttitle = $display_name.' - '.$pname;		
			$wpdb->insert($table, array(
				'post_title' => $posttitle,
				'post_name' => sanitize_title_with_dashes($posttitle),
				'post_status' => 'publish',
				'post_date' => date('Y-m-d H:i:s'),
				'post_author' => $uid,
				'post_type' => 'questionnaire'
			));
			$lastid = $wpdb->insert_id;
			update_post_meta($lastid,'entry_id',$entry['id']);
			update_post_meta($lastid,'entry_arr',$entry);
			update_post_meta($lastid,'entry_user',$uid);
			$term = term_exists( $slug, 'question-category' );
	        wp_set_post_terms( $lastid, $term, 'question-category', true );
		}
	}
}

function codex_custom_init() {
    $args = array(
      'public' => false,  // it's not public, it shouldn't have it's own permalink, and so on
	  'publicly_queryable' => true,  // you should be able to query it
	  'show_ui' => true,  // you should be able to edit it in wp-admin
	  'exclude_from_search' => true,  // you should exclude it from search results
	  'show_in_nav_menus' => false,  // you shouldn't be able to add it to menus
	  'has_archive' => false,  // it shouldn't have archive page
	  'rewrite' => false,  // it shouldn't have rewrite rules
      'label'  => 'General Information',
	  'supports' => array( 'title' ),
	  'menu_icon' => 'dashicons-media-text',
	  'menu_position' => 17
    );
    register_post_type( 'general_information', $args );
    
    $args = array(
      'public' => false,  // it's not public, it shouldn't have it's own permalink, and so on
	  'publicly_queryable' => true,  // you should be able to query it
	  'show_ui' => true,  // you should be able to edit it in wp-admin
	  'exclude_from_search' => true,  // you should exclude it from search results
	  'show_in_nav_menus' => false,  // you shouldn't be able to add it to menus
	  'has_archive' => false,  // it shouldn't have archive page
	  'rewrite' => false,  // it shouldn't have rewrite rules
      'label'  => 'Questionnaire',
	  'supports' => array( 'title' ),
	  'menu_icon' => 'dashicons-media-document',
	  'menu_position' => 18
    );
    register_post_type( 'questionnaire', $args );
    
    register_taxonomy(
		'question-category',
		'questionnaire',
		array(
			'label' => __( 'Categories' ),
			'rewrite' => array( 'slug' => 'question-category' ),
			'hierarchical' => true,
		)
	);
}
add_action( 'init', 'codex_custom_init' );

add_filter('manage_general_information_posts_columns', 'general_information_columns_head', 10);
add_filter('manage_questionnaire_posts_columns', 'general_information_columns_head', 10);
function general_information_columns_head($defaults) {
    $date = $defaults['date'];
    unset($defaults['date']);
    $defaults['author'] = 'Customer';
	$defaults['date'] = $date;
    return $defaults;
}

add_filter( 'manage_edit-questionnaire_sortable_columns', 'my_sortable_general_information_column' );
add_filter( 'manage_edit-general_information_sortable_columns', 'my_sortable_general_information_column' );
function my_sortable_general_information_column( $columns ) {
    $columns['author'] = 'author';
    return $columns;
}

function add_register_meta_boxes() {
    add_meta_box( 'question-info', __( 'Details', 'textdomain' ), 'details_display_callback', array('general_information','questionnaire') );
}
add_action( 'add_meta_boxes', 'add_register_meta_boxes' );

function details_display_callback($post){
	$post_id = $post->ID;
	$entry_id = get_post_meta($post_id,'entry_id',true);
	$entry_user = get_post_meta($post_id,'entry_user',true);
	$userdata = get_userdata($entry_user);
    $entry = get_post_meta($post_id,'entry_arr',true);
	$form_id = $entry['form_id'];
	$form = GFAPI::get_form( $form_id );
	?>
	<h3>Form Entry ID : <?php echo $entry_id; ?></h3>
	<h3>User : <a target="_blank" href="<?php echo get_edit_user_link($entry_user); ?>"><?php echo $userdata->data->display_name; ?></a></h3>
	<table cellpadding="4" cellspacing="0" class="field-table" width="100%">
		<?php
		foreach ( $form['fields'] as $field ) {
			$inputs = $field->get_entry_inputs();
			if ( $field->type=='form' ) {
			    $nform_id = $field->gpnfForm;
			    $nform = GFAPI::get_form( $nform_id );
			    $val = explode(',',$entry[$field->id]);
			    echo '<tr><td colspan="2"><table cellpadding="4" cellspacing="0" class="field-sub-table" width="98%">';
			    foreach ( $val as $v ) {
			        $nentry = GFAPI::get_entry( $v );
			        echo '<tr>';
    			    foreach ( $nform['fields'] as $field ) {
    			        $value = rgar( $nentry, (string) $field->id );
    			        echo '<td><span class="field-label 11"><b>'.$field->label.'</b></span></td>';
    			    }
    			    echo '</tr>';
    			    break;
			    }
			    foreach ( $val as $v ) {
			        $nentry = GFAPI::get_entry( $v );
			        echo '<tr>';
    			    foreach ( $nform['fields'] as $field ) {
    			        $value = rgar( $nentry, (string) $field->id );
    			        echo '<td>'.ucfirst($value).'</td>';
    			    }
    			    echo '</tr>';
			    }
			    echo '</table></td></tr>';
			}else if ( is_array( $inputs ) && !empty($inputs) ) {
				$values = array();
				$disp = false;
				foreach ( $inputs as $input ) {
					$value = rgar( $entry, (string) $input['id'] );
					if( !empty($value) ){
						$disp = true;
					}
				}
				if( $disp==true ){
					if( !empty($field->label) ){
					    echo '<tr><td><span class="field-label 22"><b>'.$field->label.'</b></span> : &nbsp;';
					}else{
					    echo '<tr><td>';
					}
					echo '<table cellpadding="4" cellspacing="0" class="field-sub-table">';
					foreach ( $inputs as $input ) {
						$value = rgar( $entry, (string) $input['id'] );
						if( !empty($value) ){
						    if( !empty($input['label']) ){
							    echo '<tr><td><span class="field-label 33"><b>'.$input['label'].'</b></span> : &nbsp;'.ucfirst($value).'</td></tr>';
						    }else{
							    echo '<tr><td colspan="2">'.ucfirst($value).'</td></tr>';
						    }
						}
					}
					echo '</table>';
					echo '</td></tr>';
				}
			} else {
				$value = rgar( $entry, (string) $field->id );
				if( $field->label!='HTML Block' && !empty($value) ){
				    if( !empty($input['label']) ){
				        if( $entry['label_'.$form_id.'_'.$field->id] ) $field->label = $entry['label_'.$form_id.'_'.$field->id];
					    echo '<tr><td><span class="field-label 44"><b>'.$field->label.'</b></span> : &nbsp;'.ucfirst($value).'</td></tr>';
				    }else{
					    echo '<tr><td colspan="2">'.ucfirst($value).'</td></tr>';
				    }
				}
			}
		}
		?>
	</table>
	<style>.field-label {width: 350px;display: inline-block;}
	.field-sub-table .field-label {width: auto;display: inline-block;}
	.field-table {font-size: 14px;}
	.field-table tr td {border-bottom: 1px solid #ccc;}
	.field-sub-table tr:last-child td {border-bottom: none;}
	table.field-sub-table {margin-left: 30px;}</style>
	<?php
}

add_filter( 'gform_pre_render_17', 'populate_form_2_data' );
add_filter( 'gform_pre_render_16', 'populate_form_2_data' );
add_filter( 'gform_pre_render_14', 'populate_form_2_data' );
add_filter( 'gform_pre_render_11', 'populate_form_2_data' );
add_filter( 'gform_pre_render_9', 'populate_form_2_data' );
add_filter( 'gform_pre_render_6', 'populate_form_2_data' );
add_filter( 'gform_pre_render_5', 'populate_form_2_data' );
add_filter( 'gform_pre_render_2', 'populate_form_2_data' );
function populate_form_2_data( $form ) {
	$uid = get_current_user_id();
    if( $form['id']==2 ){
    	$args = array(
    	  'numberposts' => 1,
    	  'post_type'   => 'general_information',
    	  'post_status'   => 'publish',
    	  'author'   => $uid
    	);
    }else if( $form['id']==5 || $form['id']==6 || $form['id']==9 || $form['id']==11 || $form['id']==14 || $form['id']==16 || $form['id']==17 ){
        if( $form['id']==5 ){
	        $slug = 'website-privacy-policy-basic-no-data';
	    }else if( $form['id']==6 ){
	        $slug = 'website-privacy-policy-basic-no-cookies';
	    }else if( $form['id']==9 ){
	        $slug = 'website-privacy-policy-first-party-cookies';
	    }else if( $form['id']==11 ){
	        $slug = 'website-privacy-policy-first-party-cookies-and-analytics';
	    }else if( $form['id']==14 ){
	        $slug = 'website-privacy-policy-first-and-third-party-cookies';
	    }else if( $form['id']==16 ){
	        $slug = 'website-privacy-policy-first-and-third-party-cookies-and-analytics';
	    }else if( $form['id']==17 ){
	        $slug = 'job-applicant-privacy-notice';
	    }
        $args = array(
    	  'numberposts' => 1,
    	  'post_type'   => 'questionnaire',
		  'post_status'   => 'publish',
		  'author'   => $uid,
		  'tax_query' => array(
		        array(
                    'taxonomy' => 'question-category',
                    'terms' => $slug,
                    'field' => 'slug'
                )
            )
    	);
    }
	 
	$posts = get_posts( $args );
	
	if( !empty($posts) ){
		$post_id = $posts[0]->ID;
		$entry = get_post_meta($post_id,'entry_arr',true);
		foreach ( $form['fields'] as $field ) {
			$values = array();
			$inputs = $field->get_entry_inputs();
			if ( $field->type=='form' ) {
			    $val = explode(',',$entry[$field->id]);
			    foreach ( $val as $v ) {
    			   if( !empty($v) ){
    			        $nested_entries[$field->id][] = $v;
    			   }
    			}
			    $nested_entries = array_filter($nested_entries);
			    $data = array(
				    'form_id'        => $form['id'],
				    'hash'           => substr( md5( uniqid( rand(), true ) ), 0, 12 ),
				    'user_id'        => get_current_user_id(),
    				'nested_entries' => $nested_entries,
    			);
			}else if ( $field->type=='checkbox' ) {
				$vals = array();
				$choicenew = array();
				foreach ( $entry as $k=>$v ) {
					if (strpos($k,$field->id.'.') !== false) {
						$vals[] = $v;
					}
				}
				if( !empty($field->choices) ){
					foreach ( $field->choices as $choice ) {
						if( in_array($choice['value'],$vals) ){
							$choice['isSelected'] = 1;
						}
						$choicenew[] = $choice;
					}
				}
				$field->choices = $choicenew;
			}else if ( is_array( $inputs ) && !empty($inputs) ) {
				$inputnew = array();
				foreach ( $inputs as $input ) {
					$value = rgar( $entry, (string) $input['id'] );
					$input['defaultValue'] = $value;
					$inputnew[] = $input;
				}
				$field->inputs = $inputnew;
			} else {
			    if( $entry['label_'.$form['id'].'_'.$field->id] ) $field->newlabel = $entry['label_'.$form['id'].'_'.$field->id];
				$field->defaultValue = rgar( $entry, (string) $field->id );
			}
		}
	}
	//$carr = json_decode( stripslashes( $_COOKIE['gpnf_form_session_'.$field->formId] ), true );print_r($carr);
	setcookie( 'gpnf_form_session_'.$form['id'], json_encode( $data ), time() + 60 * 60 * 24 * 7, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
    return $form;
}

add_filter('single_add_to_cart_text', 'woo_custom_single_add_to_cart_text');
add_filter( 'add_to_cart_text', 'woo_custom_single_add_to_cart_text' );                // < 2.1
add_filter( 'woocommerce_product_single_add_to_cart_text', 'woo_custom_single_add_to_cart_text' );  // 2.1 +
add_filter( 'woocommerce_product_add_to_cart_text', 'woo_custom_single_add_to_cart_text' );  // 2.1 +
function woo_custom_single_add_to_cart_text() {
    return __( 'Sign Up', 'woocommerce' );
}

add_filter( 'gform_field_content', function ( $field_content, $field ) {
	if (strpos($field->cssClass, 'other-op') !== false) {
	    if( $field->newlabel ) $v = $field->newlabel; else $v = '';
		$field_content = str_ireplace('</label>','<input type="text" name="label_'.$field->formId.'_'.$field->id.'" value="'.$v.'"></label>',$field_content);
	}
    return $field_content;
}, 10, 2 );

add_filter( 'gfpdf_field_label', function( $label, $field, $entry ) {
    $meta_value = gform_get_meta( $entry['id'], 'label_'.$field->formId.'_'.$field->id );
    if( $meta_value ) $label = $meta_value;
    return $label;
}, 10, 3 );

function add_top_search(){
    global $paged;
    echo '<div class="shop-top-bar">';
        echo '<div id="sidebar" class="col">';
    	if ( function_exists( 'dynamic_sidebar' ) ) {
    		dynamic_sidebar( 'woocommerce-sidebar' );
    	}
    	?>
    	<form class="woocommerce-ordering" method="get">
    	<h4>Sorting</h4>
    	<select name="orderby" class="orderby">
    					<option value="popularity">Sort by popularity</option>
    					<option value="rating" <?php echo $_GET['orderby']=='rating' ? 'selected' : ''; ?>>Sort by average rating</option>
    					<option value="date" <?php echo $_GET['orderby']=='date' ? 'selected' : ''; ?>>Sort by latest</option>
    					<option value="price" <?php echo $_GET['orderby']=='price' ? 'selected' : ''; ?>>Sort by price: low to high</option>
    					<option value="price-desc" <?php echo $_GET['orderby']=='price-desc' ? 'selected' : ''; ?>>Sort by price: high to low</option>
    			</select>
    	<input type="hidden" name="paged" value="<?php echo $paged; ?>">
    	</form>
    	<?php
    	echo '</div>';
    	echo '</div>';
}
add_action( 'nectar_shop_header_markup', 'add_top_search' );

function add_my_account_tab(){
    if( is_account_page() ){
    ?>   
        <script type="text/javascript">
        jQuery(document).ready(function(){
            jQuery(document).on('click', '.woocommerce-MyAccount-navigation-link--gdpr-assistant' ,function() {
                var url = jQuery(this).children('a').attr('href');
                window.location.replace(url);
            });
            jQuery(document).on('click', '.woocommerce-MyAccount-navigation-link--my-files' ,function() {
                var url = jQuery(this).children('a').attr('href');
                window.location.replace(url);
            });
            jQuery(document).on('click', '.woocommerce-MyAccount-navigation-link--time-clock' ,function() {
                var url = jQuery(this).children('a').attr('href');
                window.location.replace(url);
            });
            if (jQuery(window).width() < 767) {
                jQuery('.woocommerce-MyAccount-navigation-link').on('click',function() {
                    jQuery('html, body').animate({
                        scrollTop: jQuery(".woocommerce-MyAccount-content").offset().top-80
                     }, 2000);
                });
            }
        });
        </script>
    <?php
    }
    ?>
    <script type="text/javascript">
        jQuery('input#wc-gocardless-new-payment-method').attr('checked','true');
    </script>    
    <?php
}
add_action('wp_footer','add_my_account_tab');

/**
 * Check if product has attributes, dimensions or weight to override the call_user_func() expects parameter 1 to be a valid callback error when changing the additional tab
 */
add_filter( 'woocommerce_product_tabs', 'woo_rename_tabs', 98 );

function woo_rename_tabs( $tabs ) {

	global $product;
	
	if( $product->has_attributes() || $product->has_dimensions() || $product->has_weight() ) { // Check if product has attributes, dimensions or weight
		$tabs['additional_information']['title'] = __( 'Product Data' );	// Rename the additional information tab
	}
 
	return $tabs;
 
} 

function my_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url('https://www.mcs.support/wp-content/uploads/2018/10/MCS-support-invert-e1553352244932.png !') !important;
		height:65px;
		width:320px;
		background-size: 320px 65px;
		background-repeat: no-repeat;
        	padding-bottom: 30px;
        }
        .login #backtoblog a, .login #nav a { color: #fff !important; }
        body { background: #007284 !important;}
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );

function custom_pre_get_posts_query( $q ) {

    $tax_query = (array) $q->get( 'tax_query' );

    $tax_query[] = array(
           'taxonomy' => 'product_cat',
           'field' => 'slug',
           'terms' => array( 'hidden-items' ),
           'operator' => 'NOT IN'
    );


    $q->set( 'tax_query', $tax_query );

}
add_action( 'woocommerce_product_query', 'custom_pre_get_posts_query' ); 

/*function add_course_section_filter( $which ) {

    // create sprintf templates for <select> and <option>s
    $st = '<select name="course_section_%s" style="float:none;"><option value="">%s</option>%s</select>';
    $ot = '<option value="%s" %s>Section %s</option>';

    // determine which filter button was clicked, if any and set section
    $button = key( array_filter( $_GET, function($v) { return __( 'Filter' ) === $v; } ) );
    $section = $_GET[ 'course_section_' . $button ] ? $_GET[ 'course_section_' . $button ]  : -1;

    // generate <option> and <select> code
    $options = implode( '', array_map( function($i) use ( $ot, $section ) {
        return sprintf( $ot, $i, selected( $i, $section, false ), $i );
    }, range( 1, 3 ) ));
    $select = sprintf( $st, $which, __( 'Course Section...' ), $options );

    // output <select> and submit button
    echo $select;
    submit_button(__( 'Filter' ), null, $which, false);
}
add_action('restrict_manage_users', 'add_course_section_filter');*/

function add_course_section_filter( $which ) {
    $fields = acf_get_fields(8672);
    // determine which filter button was clicked, if any and set section
    $button = key( array_filter( $_GET, function($v) { return __( 'Filter' ) === $v; } ) );
    $section = $_GET[ 'cs_meta_key_' . $button ] ? $_GET[ 'cs_meta_key_' . $button ]  : -1;
    if( !empty($fields) ){
        $select = '<select name="cs_meta_key_'.$which.'" style="float:none;">';
        $select .= '<option value="">Select Meta Key</option>';
        foreach( $fields as $f ){
            $select .= '<option value="'.$f['name'].'" '.selected( $f['name'], $section, false ).'>'.$f['label'].'</option>';
        }
        $select .= '</select>';
        echo $select;
        $input = '<input name="cs_meta_val_'.$which.'" style="float:none;" value="'.$_GET[ 'cs_meta_val_' . $button ].'">';
        echo $input;
        submit_button(__( 'Filter' ), null, $which, false);
    }    
}
add_action('restrict_manage_users', 'add_course_section_filter');

function filter_users_by_course_section($query)
{
    global $pagenow;
    if (is_admin() && 'users.php' == $pagenow) {
        $button = key( array_filter( $_GET, function($v) { return __( 'Filter' ) === $v; } ) );
        if ( $_GET[ 'cs_meta_key_' . $button ] && $_GET[ 'cs_meta_val_' . $button ] ) {
            $meta_query = [['key' => $_GET[ 'cs_meta_key_' . $button ],'value' => $_GET[ 'cs_meta_val_' . $button ], 'compare' => 'LIKE']];
            $query->set('meta_key', $_GET[ 'cs_meta_key_' . $button ]);
            $query->set('meta_query', $meta_query);
        }
        //echo '<pre>';print_r($query);echo '</pre>';
    }
}
add_filter('pre_get_users', 'filter_users_by_course_section');

add_filter( 'woocommerce_email_actions', 'add_another_email_action', 9999 );
function add_another_email_action( $array ) {
	$array[]='woocommerce_order_status_completed_to_on-hold';
	return $array;
}
add_action( 'woocommerce_email', 'hook_another_email_on_hold', 9999 );
function hook_another_email_on_hold( $email_class ) {
	add_action( 'woocommerce_order_status_completed_to_on-hold_notification', array( $email_class->emails['WC_Email_Customer_On_Hold_Order'], 'trigger' ) );
}

function jobs_modify_columns( $columns ) {
  // New columns to add to table
  $new_columns = array(
	'client' => __( 'Client', '' ),
	'job_assigned' => __( 'Job Assigned', '' ),
	'job_status' => __( 'Job Status', '' )
  );
  // Combine existing columns with new columns
  $filtered_columns = array_merge( $columns, $new_columns );
  // Return our filtered array of columns
  return $filtered_columns;
}

add_filter('manage_jobs_posts_columns' , 'jobs_modify_columns');
function jobs_custom_column_content( $column ) {
  global $post;
  // Check to see if $column matches our custom column names
  switch ( $column ) {
    case 'client' :
	  $client = get_post_meta( $post->ID, 'client', true );
	  echo ( !empty( $client ) ? $client['display_name'] : '' );
      break;
    case 'job_assigned' :
	  $job_assigned = get_post_meta( $post->ID, 'job_assigned', true );
	  echo ( !empty( $job_assigned ) ? $job_assigned['display_name'] : '' );
      break;
    case 'job_status' :
	  $job_status = get_post_meta( $post->ID, 'job_status', true );
	  echo ( !empty( $job_status ) ? ucwords(str_replace('_',' ',$job_status)) : '' );
      break;
  }
}
add_action( 'manage_jobs_posts_custom_column', 'jobs_custom_column_content' );

function save_jobs_meta( $post_id ) {
    if ( $_POST['pods_meta_send_assigned_email']==1 ) {
        $email = new WC_Emails();
        
        update_post_meta( $post_id, 'send_assigned_email', '' );
        $job_assigned = get_post_meta( $post_id, 'job_assigned', true );
        $to = $job_assigned['user_email'];
        //$to = 'muzammilmotiwala20@gmail.com';
        $subject = 'Job Has Been Assigned To You';
        ob_start();
        $body = '<style>';
        ob_start();
        $body .= wc_get_template( 'emails/email-styles.php' );
        $body .= ob_get_clean();
        $body .= '</style>';
        $body .= wc_get_template( 'emails/email-header.php', array( 'email_heading' => 'Job Has Been Assigned To You' ) );
        $body .= ob_get_clean();
        $body .= 'Job is assinged to you.<br><br>
        Job link - <a href="'.get_edit_post_link($post_id).'">Click Here</a>';
        ob_start();
        $body .= wc_get_template( 'emails/email-footer.php' );
        $body .= ob_get_clean();
        
        echo $body;
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail( $to, $subject, $body, $headers );
    }
}
add_action( 'save_post', 'save_jobs_meta', 100 );

add_filter( 'manage_edit-shop_order_columns', 'add_payment_method_column', 20 );
function add_payment_method_column( $columns ) {
 $new_columns = array();
 foreach ( $columns as $column_name => $column_info ) {
 $new_columns[ $column_name ] = $column_info;
 if ( 'order_total' === $column_name ) {
 $new_columns['order_payment'] = __( 'Payment Method', 'my-textdomain' );
 }
 }
 return $new_columns;
}
add_action( 'manage_shop_order_posts_custom_column', 'add_payment_method_column_content' );
function add_payment_method_column_content( $column ) {
 global $post;
 if ( 'order_payment' === $column ) {
 $order = wc_get_order( $post->ID );
 echo $order->payment_method_title;
 }
}
?>