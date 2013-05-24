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
  $cache = $this->find_cache();
  $id = 0; 
  ?>
  
  <form action="" method="post" accept-charset="utf-8">
  
    <div class="tablenav top">
      <div class="alignleft actions">
        <select name="bulk">
          <option value="-1" selected="selected">Bulk Actions</option>
          <option value="delete">Delete</option>
        </select>
        <input type="submit" name="" id="doaction" class="button action" value="Apply">
      </div>
      <br class="clear">
    </div>

    <?php wp_nonce_field('mn_combine_update', 'mn_combine');?>
    
    <table class="form-table" id="cache-files">
      <thead>
        <tr>
          <th width="200">
          </th>
          <td>
            <a href="#" class="select-all" data-target="#cache-files">Select</a>
            /
            <a href="#" class="deselect-all" data-target="#cache-files">Deselect</a>
            All
          </td>
        </tr>
      </thead>
      <tbody>
        <tr valign="top">
          <th scope="row">Cached CSS files</th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span>CSS cache files</span>
              </legend>
              
              <?php 
              if( isset($cache['css']) ) 
                foreach($cache['css'] as $asset): $asset['file'] = trim($asset['file']);?>
                
                <label for="<?php echo $id; ?>">
                  <input name="delete[<?php echo $asset['file']; ?>]" type="checkbox" id="<?php echo $id; ?>" value="1"/>
                  <?php echo basename($asset['file']); ?> <code><?php echo $asset['compiled']; ?></code>
                </label>
                <br/>
                
              <?php $id++; endforeach; ?>
              
            </fieldset>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row">Cached Javascript files</th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span>Javascript cache files</span>
              </legend>
              
              <?php 
              if( isset($cache['js']) ) 
                foreach($cache['js'] as $asset): $asset['file'] = trim($asset['file']);?>
                
                <label for="<?php echo $id; ?>">
                  <input name="delete[<?php echo $asset['file']; ?>]" type="checkbox" id="<?php echo $id; ?>" value="1"/>
                  <?php echo basename($asset['file']); ?> <code><?php echo $asset['compiled']; ?></code>
                </label>
                <br/>
                
              <?php $id++; endforeach; ?>
              
            </fieldset>
          </td>
        </tr>
      </tbody>
    </table>

  </form>

</div>
