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
  $compression = get_option( 'mn_compression_engine', 'none' );
  $id = 0;
  ?>
  
  <form action="" method="post" accept-charset="utf-8">
    
    <?php wp_nonce_field('mn_combine_update', 'mn_combine');?>
    
    <table class="form-table" id="combine-files">
      <thead>
        <tr>
          <th width="200">
          </th>
          <td>
            <a href="#" class="select-all" data-target="#combine-files">Select</a>
            /
            <a href="#" class="deselect-all" data-target="#combine-files">Deselect</a>
            All
          </td>
        </tr>
      </thead>
      <tbody>
        <tr valign="top">
          <th scope="row">Files To Combine</th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span>Javascript files to combine</span>
              </legend>
              
              <?php 
              $wp = false;
              if( isset($assets['js']) ) 
                foreach($assets['js'] as $asset): $asset = trim($asset);?>
                
                <?php if( !$wp && strstr($asset, '/wp-includes') ): $wp = true;?>
                  <h3>WordPress core files</h3>
                  <div class="wp-includes">
                <?php endif; ?>
                
                <label for="<?php echo $id; ?>">
                  <input name="combine[<?php echo $id; ?>][<?php echo $asset; ?>]" type="hidden" value="0"/>
                  <input name="combine[<?php echo $id; ?>][<?php echo $asset; ?>]" type="checkbox" id="<?php echo $id; ?>" value="1" <?php if( in_array( $asset, $current['combine']['js'] ) ) echo 'checked="checked"'; ?>/>
                  <?php echo basename($asset); ?> <code><?php echo str_replace( str_replace( "\\", "/", ABSPATH ), "", $asset ); ?></code>
                </label>
                <br/>
                
              <?php $id++; endforeach; ?>
              
                <?php if( !$wp ): ?>
                  <div>
                <?php endif; ?>
                
                </div><?php //end wp includes; ?>
              
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>
    
    <p>&nbsp;</p>
    
    <table class="form-table" id="compress-files">
      <thead>
        <tr>
          <th width="200">
          </th>
          <td>
            <a href="#" class="select-all" data-target="#compress-files">Select</a>
            /
            <a href="#" class="deselect-all" data-target="#compress-files">Deselect</a>
            All
          </td>
        </tr>
      </thead>
      <tbody>
        <tr valign="top">
          <th scope="row">Files To Compress</th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span>Javascript files to compress</span>
              </legend>
              
              <?php 
              $wp = false;
              if( isset($assets['js']) ) 
                foreach($assets['js'] as $asset): $asset = trim($asset);?>
                
                <?php if( !$wp && strstr($asset, '/wp-includes') ): $wp = true;?>
                  <h3>WordPress core files</h3>
                  <div class="wp-includes">
                <?php endif; ?>
                
                <label for="<?php echo $id; ?>">
                  <input name="compress[<?php echo $id; ?>][<?php echo $asset; ?>]" type="hidden" value="0"/>
                  <input name="compress[<?php echo $id; ?>][<?php echo $asset; ?>]" type="checkbox" id="<?php echo $id; ?>" value="1" <?php if( in_array( $asset, $current['compress']['js'] ) ) echo 'checked="checked"'; ?>/>
                  <?php echo basename($asset); ?> <code><?php echo str_replace( str_replace( "\\", "/", ABSPATH ), "", $asset ); ?></code>
                </label>
                <br/>
                
              <?php $id++; endforeach; ?>
              
                <?php if( !$wp ): ?>
                  <div>
                <?php endif; ?>
                
                </div><?php //end wp includes; ?>
              
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>
    
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
  </form>

</div>
