<?php
/**
 * Plugin Name: I Pay You
 * Plugin URI: https://afiwai.com/categorie-produit/telechargements/plugins/
 * Description: Create a PayPalMe link shortcode with a custom amount.
 * Version: 1.1
 * Author: AFIWAI DESIGN
 * Author URI: https://afiwai.com/
 * Version:     1.1
 * Text Domain: ipayou
 * Domain Path: /languages/
 *
 * Text Domain: i-pay-you
 *
 * @package I Pay You
**/

function ipy_load_translation() {
 $domain = 'ipayou';
 $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
 load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/', $locale );
}
add_action( 'plugins_loaded', 'ipy_load_translation' );


function ipy_register_page_template( $templates ) {
    $templates['afiwai-design-page.php'] = 'AFIWAI DESIGN Page';
    return $templates;
}
add_filter( 'theme_page_templates', 'ipy_register_page_template' );

function ipy_assign_page_template( $template ) {
    if ( is_page_template( 'afiwai-design-page.php' ) ) {
        $template = dirname( __FILE__ ) . '/afiwai-design-page.php';
    }
    return $template;
}
add_filter( 'template_include', 'ipy_assign_page_template', 99 );


function ipy_paypalme_link_shortcode( $atts ) {
  $atts = shortcode_atts( array(
    'text' => 'Pay with PayPal',
    'target' => '_blank',
    'amount' => '',
    'username' => ''
  ), $atts );
	
  if ( isset( $atts['username'] ) ) {
    $paypalme_username = sanitize_text_field( $atts['username'] );
    $paypalme_url = 'https://www.paypal.com/paypalme/' . $paypalme_username . '/' . $atts['amount'];
  } else {
    $paypalme_username = get_option( 'ipayou_paypalme_username', '' );
    $paypalme_url = 'https://www.paypal.com/paypal.me/' . $paypalme_username . '/' . $atts['amount'];
  }
	    $output = '<a href="' . esc_url( $paypalme_url ) . '" target="' . esc_attr( $atts['target'] ) . '">' . esc_html( $atts['text'] ) . '</a>';

  return $output;
}

add_shortcode( 'ipayou_paypalme_link', 'ipy_paypalme_link_shortcode' );
	
function ipy_generate_paypalme_link() {
  $result_html = '';
  $paypalme_username = get_option( 'ipayou_paypalme_username', '' );
  if ( isset( $_POST['ipy_paypalme_amount'] ) ) {
    $amount = floatval( $_POST['ipy_paypalme_amount'] );
    if ( $amount > 0 ) {
      $paypalme_url = 'https://www.paypal.com/paypalme/' . $paypalme_username . '/' . $amount;
  $result_html = '<a href="' . esc_url( $paypalme_url ) . '" target="_blank" class="button"><img src="' . plugins_url( 'asset/images/fleche.gif', __FILE__ ) . '"> PayPalMe Link</a>';

	  $signature_html = '<hr style="border-top: 1px solid #ccc; margin-top: 15px;"><p style="font-size: 12px; margin-top: 15px;"> "I Pay You" by <a href="https://afiwai.com/categorie-produit/telechargements/plugins/">AFIWAI DESIGN</a></p>';
      $result_html .= $signature_html;
    } else {
      $result_html = '<p>Invalid amount entered.</p>';
    }
  }

$form_html = '<form method="post" class="ipayou-form">
    <label for="ipy_paypalme_amount">Enter the amount to be paid (in euros):</label>
	<br>
    <input type="number" id="ipy_paypalme_amount" name="ipy_paypalme_amount" step="0.01" min="0" required>
    <br>
    <button type="submit">Generate PayPalMe Link</button>
  </form>';

  return $form_html . $result_html;
}


add_shortcode( 'ipayou_generate_paypalme_link', 'ipy_generate_paypalme_link' );

// Add the options page
function ipy_options_page() {
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

  if ( isset( $_POST['paypalme_username'] ) && wp_verify_nonce( $_POST['ipayou_save_settings_nonce'], 'ipayou_save_settings' ) ) {
    update_option( 'ipayou_paypalme_username', sanitize_text_field( $_POST['paypalme_username'] ) );
    echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
  }

  $paypalme_username = get_option( 'ipayou_paypalme_username', '' );
  $nonce = wp_create_nonce( 'ipayou_save_settings' );

  $output = '<div class="wrap">';
  $output .= '<h1>I Pay You - PayPalMe Options</h1>';
  $output .= '<h2>Main features</h2>';
  $output .= '<ul>';
  $output .= '<li>"I Pay You" allows your members or visitors to donate or pay you by generating a PayPalMe link themselves with the amount they wish to send to your PayPal account.</li>';
  $output .= '<li><b>Step 1:</b> Enter your PayPal ID to generate the link to your account.
             <br>Example: https://www.paypal.com/paypalme/<b>YOUR_ID</b>
             <br>So your ID is: <b>YOUR_ID</b></p></li>';
  $output .= '<li><b>Step 2:</b> Choose a page where you want to insert the form that will generate the payment link.
             <br>Visitors will only have to enter the amount to pay and validate the form to generate the payment link.
			 <br>
             <br>Here is the shortcode for the form: <b>[ipayou_generate_paypalme_link]</b></p></li>';
  $output .= '<li><b>Step 3:</b> When the visitor clicks on the generation form, it will create a customized payment link to your PayPal account.
             <br>This link will be displayed as a clickable link.</b>
             <br>To display the clickable link, please insert this shortcode on your form page.</b>
			 <br>
			 </p></li>';	
  $output .= '<hr><br>'; // Line separator added here
  $output .='<li>Enter your PayPalMe username to receive payments
             <br>More information on creating a PayPalMe link: <a href="https://www.paypal.com/webapps/mpp/paypal-me" target="_blank">PayPal site</a></p></li>';
  $output .= '</ul>';
  $output .= '<form method="post">';
  $output .= '<input type="hidden" name="ipayou_save_settings_nonce" value="' . $nonce . '" />';
  $output .= '<table class="form-table">';
  $output .= '<tr>';
  $output .= '<th scope="row"><label for="paypalme_username">PayPalMe Username</label></th>';
  $output .= '<td><input type="text" name="paypalme_username" id="paypalme_username" value="' . esc_attr( $paypalme_username ) . '" /></td>';
  $output .= '</tr>';
  $output .= '</table>';
  $output .= '<p><input type="submit" class="button button-primary" value="Save Settings" /></p>';
  $output .= '</form>';
  $output .= '</div>';
	
  $output .= '</ul>';
  $output .= '<hr>';
  $output .= '<p>Thank you for using my extension. To thank me, please advertise for me.
  <br>I create websites, graphics, and videos. Here is my website <a href="https://afiwai.com/" target="_blank">AFIWAI DESIGN</a>.</p>';
  $output .= '</div>';

  echo $output;
	
// Enqueue your custom stylesheet
  wp_enqueue_style( 'ipayou-styles', plugin_dir_url( __FILE__ ) . 'ipayou-styles.css' );
}


// Add the options page
function ipy_afiwai_options_page() {
  if ( ! current_user_can( 'manage_options' ) ) {
    return;
  }

  if ( isset( $_POST['paypalme_username'] ) && wp_verify_nonce( $_POST['ipayou_save_settings_nonce'], 'ipayou_save_settings' ) ) {
    update_option( 'ipayou_paypalme_username', sanitize_text_field( $_POST['paypalme_username'] ) );
    echo '<div class="notice notice-success"><p>Settings saved.</p></div>';
  }

  $paypalme_username = get_option( 'ipayou_paypalme_username', '' );
  $nonce = wp_create_nonce( 'ipayou_save_settings' );

  $output = '<div class="wrap">';
  $output .= '<h1>AFIWAI DESIGN - Careers in visual arts</h1>';
  $output .= '<h2>Who are we?</h2>';
  $output .= '<ul>';
  $output .= '<li><i>This extension is brought to you by <b>AFIWAI DESIGN</b>, an image creation company specializing in the creation of showcase and e-commerce websites, graphic design, illustration, and the production of videos and aerial videos. Some of our extensions have been developed specifically to meet the needs of our clients and are made available on our website to enrich the library of WordPress plugins.
   </i><br></li>';
  $output .= '<li><i>If you would like to thank us, it\'s easy: recommend us to your friends and professional contacts!</i>
  <br>
    <br></li>';
	
  $output .= '<hr><br>'; // Line separator added here
$output .= '<li><span class="u">To contact us, please visit our website:</span>
<br></li>';
  $output .= '<li>To access our site in English, please go to: <a href="https://afiwai-com.translate.goog/?_x_tr_sl=fr&_x_tr_tl=en&_x_tr_hl=fr&_x_tr_pto=wapp" target="_blank">Google Translate</a>.
   <br></li>';
  $output .= '<li>Other extensions are also available on our page: <a href="https://afiwai.com/nos-plugins/" target="_blank">Plugins page</a>.
   <br></li>';
  $output .= '</div>';

  echo $output;
	
// Enqueue your custom stylesheet
  wp_enqueue_style( 'ipayou-styles', plugin_dir_url( __FILE__ ) . 'ipayou-styles.css' );
}


add_action('admin_menu', 'ipy_add_to_afiwai_design_menu');

function ipy_add_to_afiwai_design_menu() {
    global $menu;

    $menu_slug = 'afiwai-design';
    $menu_exists = false;

    // Vérifier si le menu existe déjà
    foreach ( $menu as $item ) {
        if ( $menu_slug == $item[2] ) {
            $menu_exists = true;
            break;
        }
    }

    // Si le menu n'existe pas, le créer
    if ( ! $menu_exists ) {
        add_menu_page(
            'AFIWAI DESIGN',
            'AFIWAI DESIGN',
            'manage_options',
            $menu_slug,
            'afiwai_menu_html'
        );

// Pour appeler une page externe sur la page AFIWAI DESIGN //
		
function afiwai_menu_html() {
    $content = file_get_contents('https://afiwai.com/nos-plugins/');

    // Extraire la partie du contenu entre le début et la fin spécifiés
    $start_marker = '<div id="main">';
    $end_marker = '<footer';
    $start_position = strpos($content, $start_marker);
    $end_position = strpos($content, $end_marker, $start_position + strlen($start_marker));
    $extracted_content = substr($content, $start_position, $end_position - $start_position);

    echo $extracted_content;
}




// Sous-menu //
    }

    // Ajouter l'extension "I Pay You" comme sous-menu
    add_submenu_page(
        $menu_slug,
        'I Pay You - PayPalMe Options',
        'I Pay You',
        'manage_options',
        'ipayou-paypalme-options',
        'ipy_options_page'
    );
}
