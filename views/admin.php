<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package    MnCombine
 * @author     Michael Neil
 * @license    GPL-2.0+
 * @link       http://mneilsworld.com/mncombine
 * @since      1.0.0
 */
?>
<div class="wrap">

	<?php screen_icon(); ?>
	<h2 class="nav-tab-wrapper">
    <a href="<?php echo admin_url(get_admin_page_parent() . "?page=$this->plugin_slug"); ?>" class="nav-tab <?php if( !isset($_GET['action'] ) ) echo 'nav-tab-active'; ?>">General Settings</a>
    <a href="<?php echo admin_url(get_admin_page_parent() . "?page=$this->plugin_slug&action=cache"); ?>" class="nav-tab <?php if( isset($_GET['action'] ) && 'cache' === $_GET['action'] ) echo 'nav-tab-active'; ?>">Cache</a>
    <a href="<?php echo admin_url(get_admin_page_parent() . "?page=$this->plugin_slug&action=js"); ?>" class="nav-tab <?php if( isset($_GET['action'] ) && 'js' === $_GET['action'] ) echo 'nav-tab-active'; ?>">Javascript</a>
    <a href="<?php echo admin_url(get_admin_page_parent() . "?page=$this->plugin_slug&action=css"); ?>" class="nav-tab <?php if( isset($_GET['action'] ) && 'css' === $_GET['action'] ) echo 'nav-tab-active'; ?>">CSS</a>
  </h2>
	
  <?php 
    if( is_wp_error($this->errors) ):
      $errors = $this->errors->get_error_messages();
      ?>
      <div id="setting-error-settings" class="<?php echo $this->errors->get_error_data(); ?> settings-error">
        <?php if( is_array($errors) && !empty($errors) )
        foreach( $errors as $error ): ?>
        <p>
          <?php echo $error; ?>
        </p>
        <?php endforeach; ?>
      </div>
      <?php
    endif;
  ?>   

  <?php 
  $assets = $this->find_assets(); 
  $current = get_option( 'mn_comine_assets', $this->default );
  $compression = get_option( 'mn_compression_engine', $this->compression_engine );
  $compile_mode = get_option( 'mn_compile_mode', $this->compile_mode );
  $force_combine = get_option( 'mn_force_combine', $this->force_combine );
  $css_compression = get_option( 'mn_css_compression', $this->css_compression );
  //$compress_js_single = get_option( 'mn_compress_js_single', $this->compress_js_single );
  $id = 0;
  ?>
  
  <form action="" method="post" accept-charset="utf-8">
    
    <?php wp_nonce_field('mn_combine_update', 'mn_combine');?>
    
    <table class="form-table">
      <tbody>
        <tr valign="top">
          <th scope="row">Javascript Compression Engine</th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span>choose which javascript engine to use when compressing</span>
              </legend>
              <label for="none">
                <input name="compression_engine" type="radio" id="none" value="none" <?php if( $compression == "none" )echo 'checked="checked"'; ?>/>
                No Compression
              </label>
              <br/>
              <label for="closure">
                <input name="compression_engine" type="radio" id="closure" value="google_closure" <?php if( $compression == "google_closure" )echo 'checked="checked"'; ?>/>
                Google Closure <a href="https://developers.google.com/closure/compiler/" target="_blank">learn more</a>
              </label>
              <br/>
              <label for="jsmin">
                <input name="compression_engine" type="radio" id="jsmin" value="js_min" <?php if( $compression == "js_min" )echo 'checked="checked"'; ?>/>
                JSMin <small>Not recommended but it still works</small> <a href="https://github.com/rgrove/jsmin-php/" target="_blank">learn more</a>
              </label>
              <br/>
              
            </fieldset>
          </td>
        </tr>
        <?php /* not ready for primetime. This will fail miserably with dependency order ?>
        <tr valign="top">
          <th scope="row">Compress Javascript Individually</th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span>choose whether to combine javascript then compress or compress then combine</span>
              </legend>
              <label for="compress_js_single_0">
                <input name="compress_js_single" type="radio" id="compress_js_single_0" value="0" <?php if( $compress_js_single == "0" )echo 'checked="checked"'; ?>/>
                No <small>(combine files then compress all js at once)</small>
              </label>
              <br/>
              <label for="compress_js_single_1">
                <input name="compress_js_single" type="radio" id="compress_js_single_1" value="1" <?php if( $compress_js_single == "1" )echo 'checked="checked"'; ?>/>
                Yes <small>(compress each js file separately then combine)</small>
              </label>
              <br/>
              
            </fieldset>
          </td>
        </tr>
        <?php */ ?>
        <tr valign="top">
          <th scope="row">Compress CSS</th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span>choose whether or not to compress the css</span>
              </legend>
              <label for="css_compress_0">
                <input name="css_compression" type="radio" id="css_compress_0" value="0" <?php if( $css_compression == "0" )echo 'checked="checked"'; ?>/>
                No
              </label>
              <br/>
              <label for="css_compress_1">
                <input name="css_compression" type="radio" id="css_compress_1" value="1" <?php if( $css_compression == "1" )echo 'checked="checked"'; ?>/>
                Yes
              </label>
              <br/>
              
            </fieldset>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row">Mode</th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span>Choose a mode to determine when to compress</span>
              </legend>
              <label for="none">
                <input name="compile_mode" type="radio" id="none" value="development" <?php if( $compile_mode == "development" )echo 'checked="checked"'; ?>/>
                Development
              </label>
              <br/>
              <label for="closure">
                <input name="compile_mode" type="radio" id="closure" value="production" <?php if( $compile_mode == "production" )echo 'checked="checked"'; ?>/>
                Production
              </label>
              <br/>
              
            </fieldset>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row">Force Combine</th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span>Force scripts queued to load in the header or footer only</span>
              </legend>
              <label for="none">
                <input name="force_combine" type="radio" id="none" value="none" <?php if( $force_combine == "none" )echo 'checked="checked"'; ?>/>
                Do not force
              </label>
              <br/>
              <label for="header">
                <input name="force_combine" type="radio" id="header" value="header" <?php if( $force_combine == "header" )echo 'checked="checked"'; ?>/>
                In the header <a href="#" class="read-help">learn more</a>
              </label>
              <br/>
              <label for="footer">
                <input name="force_combine" type="radio" id="footer" value="footer" <?php if( $force_combine == "footer" )echo 'checked="checked"'; ?>/>
                In the footer <a href="#" class="read-help">learn more</a>
              </label>
              <br/>
              
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>
    
    
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
  </form>

</div>
