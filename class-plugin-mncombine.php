<?php
/**
 * MnCombine
 *
 * @package   MnCombine
 * @author    Michael Neil <mneil@mneilsworld.com>
 * @copyright 2013 MneilsWorld
 * @license   GPL-2.0+
 * @link      http://mneilsworld.com/
 */

/**
 * MnCombine
 *
 * @package MnCombine
 * @author  Michael Neil <mneil@mneilsworld.com>
 */
class MnCombine {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $version = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'mn-combine';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;
  
  /**
   * Stores errors for display
   * 
   * @since    1.0.0
   * 
   * @var      object
   */
  protected $errors = null;//error|updated
  
  /**
   * Stores a reference to our upload directory
   * 
   * @since 1.0.0
   * 
   * @var string
   */
  protected $upload_dir = "mn_combine";
  
  /**
   * Stores a reference to wp upload directory
   * 
   * @since 1.0.0
   * 
   * @var array
   */
  protected $uploads = array();
  
  /**
   * Temporarily stores a directory path for matching css paths
   * 
   * @since 1.0.0
   * 
   * @var string
   */
  protected $dir = "";
  
  /**
   * Stores the combined assets and their handles for lookup on compress
   * 
   * @since 1.0.0
   * 
   * @var array
   */
  protected $combined = array();
  
  /**
   * Stores the default compression mode
   * 
   * @since 1.0.0
   * 
   * @var string
   */
  protected $compression_engine = 'google_closure';
  
  /**
   * Stores the default compile mode
   * 
   * @since 1.0.0
   * 
   * @var string
   */
  protected $compile_mode = 'production';
  
  /**
   * Stores the default force combine option
   * 
   * @since 1.0.0
   * 
   * @var string
   */
  protected $force_combine = 'none';
  
  /**
   * Stores the default parsing structure for stored data
   * 
   * @since 1.0.0
   * 
   * @var array
   */
  protected $default = array(
    'combine' => array(
      'css'=>array(), 
      'js'=>array()
    ),
    'compress' => array(
      'css'=>array(), 
      'js'=>array()
    )
  );
   

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
    
		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Enqueue admin styles and scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Enqueue public style and scripts.
		//add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		//add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		if( is_admin() )
      return;
    
		add_action( 'wp_print_scripts', array( $this, 'wp_print_scripts' ), 99999 );//we want to do this dead last
		add_action( 'print_footer_scripts', array( $this, 'print_footer_scripts' ), 99999 );
    
    add_action( 'wp_print_styles', array( $this, 'wp_print_styles' ), 99999 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		// TODO: Define activation functionality here
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		// TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
    
    $this->uploads = wp_upload_dir();
	}

	/**
	 * Enqueue admin-specific style sheets.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), $this->version );
		}

	}

	/**
	 * Enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->version, true );
		}

	}

	/**
	 * Enqueue public-facing style sheets.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), $this->version );
	}

	/**
	 * Enqueues public-facing script files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/public.js', __FILE__ ), array( 'jquery' ), $this->version );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		/*
		 * TODO:
		 *
		 * Change 'Page Title' to the title of your plugin admin page
		 * Change 'Menu Text' to the text for menu item for the plugin settings page
		 * Change 'plugin-name' to the name of your plugin
		 */
		$this->plugin_screen_hook_suffix = add_plugins_page(
			__('Mn Combine', 'MnCombine'),
			__('Asset Combine', 'MnCombine'),
			'read',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);
    
    add_action("load-{$this->plugin_screen_hook_suffix}", array( $this, 'add_help_tab' ));
    //add_action("load-{$this->plugin_screen_hook_suffix}", array( $this, 'add_screen_options' ));
	}
  public function add_help_tab()
  {
    /*
     * Check if current screen is My Admin Page
     * Don't add help tab if it's not
     */
    $screen = get_current_screen();
    if ( $screen->id != $this->plugin_screen_hook_suffix )
      return;

    // Add my_help_tab if current screen is My Admin Page
    $screen->add_help_tab( array(
        'id'  => 'mn_combine_description',
        'title' => __('Description'),
        'content' => '<p>' . __( 'Any stylesheets or javascript files that are registered with 
            wp_enqueue_script or wp_enqueue_style can be intercepted and compressed and combined 
            in order to both reduce the number of requests to your server and minimize 
            the data transfer out of your server. This increases page
            performance by decreasing the load on your server.' ) . '</p>' . '<p>' . __( 'You 
            can also restrict the loading of files to certain pages, posts, or post types in 
            order to further reduce bandwidth on high traffic sites. Many plugin developers 
            will simply enqueue their scripts which means these files can be included on 
            every single page including those that do not require the code. Sometimes 
            this can cause conflicts on the page and increases load time on pages.' ) . '</p>',
    ) );
    $screen->add_help_tab( array(
        'id'  => 'mn_combine_general',
        'title' => __('General Settings'),
        'content' => '<p>' . '<strong>' . __('Javascript Compression Engine ') . '</strong>' . __( ': determine
          the compression engine to use when compressing javascript files' ) . '</p>' . '<p>'
           . '<strong>' . __('Mode ') . '</strong>' . 
          __( ' : Prodution mode will only
          compile the files neccessary for a page on the first request and cache those files.
          All subsequent requests will serve those cache files until either a new dependency
          is queued or the cache file is removed. Development mode will monitor the files
          last change time and recompile the assets on any page request where the files data
          has been modified.' ) . '<em><strong>' . __(' NOTE: ') . '</strong>' . __(' development mode will not monitor changes
          made to css files that are included by an @import statement ') . '</em></p>'
           . '<strong>' . __('Force combine ') . '</strong>' .
          __( ' : footer will force all javascript to load in the footer while header
          will force all queued javascript to be loaded in the footer. Forcing files queued for the header into the footer
          can cause some scripts to fail or dependencies to be missed if javascript is written inline in. 
          Forcing scripts into the header can cause scripts queued late to still remain in the footer.
          Use this to get the best load times possible but beware that it can break your site when enabled and probably isn\'t necessary.' ) . '</p>',
    ) );
  }
  /**
   * Adds screen options
   * 
   * @since 1.0.0
   */
  public function add_screen_options()
  {     
    /*
     * Check if current screen is My Admin Page
     * Don't add help tab if it's not
     */
    $screen = get_current_screen();
    if ( $screen->id != $this->plugin_screen_hook_suffix )
      return;
    
    $args = array(
      'label' => __('Members per page', 'MnCombine'),
      'default' => 10,
      'option' => 'some_option'
    );
    add_screen_option( 'per_page', $args );
  }

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
	  if ( !empty($_POST) )
    {
      if( wp_verify_nonce( $_POST['mn_combine'], 'mn_combine_update' ) )
        $this->save_data();
      else
       $this->errors = new WP_Error('mn_combine', 'Sorry, your nonce did not verify.', 'error');
      
    }
    if( !isset( $_GET['action'] ) )
		  include_once( 'views/admin.php' );
    elseif( "js" === $_GET['action'] )
      include_once( 'views/js.php' );
    elseif( "css" === $_GET['action'] )
      include_once( 'views/css.php' );
    elseif( "cache" === $_GET['action'] )
      include_once( 'views/cache.php' );
    
	}
  
  /**
   * Saves admin form data
   * 
   * @since    1.0.0
   */
  protected function save_data()
  {
    $this->errors = new WP_Error();
    $save = $this->default;
    
    $assets = get_option( 'mn_comine_assets', $this->default );
    $this->errors->add('mn_combine', 'Settings updated', 'updated');
    
    if( isset( $_POST['combine'] ) )
      foreach( $_POST['combine'] as $c )
        foreach( $c as $key => $val )
          if( $val === "1" )
            if( strstr( $key, '.css' ) )
              $save['combine']['css'][] = $key;
            else
              $save['combine']['js'][] = $key;

    if( isset( $_POST['compress'] ) )
      foreach( $_POST['compress'] as $c )
        foreach( $c as $key => $val )
          if( $val === "1" )
            if( strstr( $key, '.css' ) )
              $save['compress']['css'][] = $key;
            else
              $save['compress']['js'][] = $key;
    
    if( !isset( $_GET['action'] ) )
    {
      update_option( 'mn_compression_engine', $_POST['compression_engine'] );
      update_option( 'mn_compile_mode', $_POST['compile_mode'] );
      update_option( 'mn_force_combine', $_POST['force_combine'] );
    }
    elseif( "js" === $_GET['action'] )
    {
      $save['combine']['css'] = $assets['combine']['css'];
      $save['compress']['css'] = $assets['compress']['css'];
      update_option( 'mn_comine_assets', $save );
    }
    elseif( "css" === $_GET['action'] )
    {
      $save['combine']['js'] = $assets['combine']['js'];
      $save['compress']['js'] = $assets['compress']['js'];
      update_option( 'mn_comine_assets', $save );
    }
    elseif( "cache" === $_GET['action'] )
    {
      if( !empty( $_POST['delete'] ) )
        foreach( $_POST['delete'] as $file => $delete )
          if( !@unlink( $file ) )
            $this->errors->add('mn_combine', "Unable to remove $file", 'error');    
    }

  }
  /**
   * Grabs the cache files
   * 
   * @since 1.0.0
   */
  private function find_cache()
  {
    $directory = new RecursiveDirectoryIterator( $this->uploads['basedir'] . '/' . $this->upload_dir );
    // Filter css/js files . although in this case these should be all that exist
    $filter = new FilenameFilter($directory, '/\.(?:css|js)$/');
    $c = array();
        
    foreach(new RecursiveIteratorIterator($filter) as $file)
    {
      if($f = fopen($file, 'r'))
      {
        $line = fgets($f);
        fclose($f);
      }      
      if( strstr($file, ".css") )
        $cache['css'][] = array( 'file' => str_replace("\\", "/", $file ) . PHP_EOL, 'compiled' => $line );
      else
        $cache['js'][] = array( 'file' => str_replace("\\", "/", $file ) . PHP_EOL, 'compiled' => $line );
          
    }
    return $cache;
  }
  /**
   * Recursively scours the wp plugins and theme folder for assets we can use
   * 
   * @since 1.0.0
   */
  private function find_assets()
  {
    $directory = new RecursiveDirectoryIterator(WP_PLUGIN_DIR);
    // Filter out ".Trash*" folders
    $filter = new DirnameFilter($directory, '/^(?!'.dirname( plugin_basename( __FILE__ ) ).')/');
    // Filter css/js files 
    $filter = new FilenameFilter($filter, '/\.(?:css|js)$/');
    
    $assets = array();
    
    foreach(new RecursiveIteratorIterator($filter) as $file) {
      if( strstr($file, ".css") )
        $assets['css'][] = str_replace("\\", "/", $file ) . PHP_EOL;
      
      else
        $assets['js'][] = str_replace("\\", "/", $file ) . PHP_EOL;
    }
    $assets = $this->find_theme_assets( $assets );
    $assets = $this->find_wp_assets( $assets );
   
    return $assets;
  }
  /**
   * Finds asset files in the current activated theme
   * 
   * @since 1.0.0
   * 
   * @return array
   */
  private function find_theme_assets( $assets)
  {
    $directory = new RecursiveDirectoryIterator(get_stylesheet_directory());
    // Filter out ".Trash*" folders
    $filter = new DirnameFilter($directory, '/^(?!'.dirname( plugin_basename( __FILE__ ) ).')/');
    // Filter css/js files 
    $filter = new FilenameFilter($filter, '/\.(?:css|js)$/');
    
    foreach(new RecursiveIteratorIterator($filter) as $file) {
      if( strstr($file, ".css") )
        $assets['css'][] = str_replace("\\", "/", $file ) . PHP_EOL;
      
      else
        $assets['js'][] = str_replace("\\", "/", $file ) . PHP_EOL;
    }

    return $assets;
  }
  /**
   * Finds asset files included from wp
   * 
   * @since 1.0.0
   * 
   * @return array
   */
  private function find_wp_assets( $assets )
  {
    $directory = new RecursiveDirectoryIterator( ABSPATH . "wp-includes" );
    // Filter css/js files 
    $filter = new FilenameFilter($directory, '/\.(?:css|js)$/');
    
    foreach(new RecursiveIteratorIterator($filter) as $file) {
      if( strstr($file, ".css") )
        $assets['css'][] = str_replace("\\", "/", $file ) . PHP_EOL;
      
      else
        $assets['js'][] = str_replace("\\", "/", $file ) . PHP_EOL;
    }

    return $assets;
  }
  /**
   * Action hook for wp_print_scripts
   * 
   * @since 1.0.0
   */
  public function wp_print_scripts()
  {
    do_action('mn_print_scripts');
    global $wp_scripts, $auto_compress_scripts;
     
    $header = array();
    $footer = array();
    $localize = array();//store the localize scripts data
    $assets = get_option( 'mn_comine_assets', $this->default );//get the list of files we can compress/combine
    $force_combine = get_option( 'mn_force_combine', $this->force_combine );
    $compile_mode = get_option( 'mn_compile_mode', $this->compile_mode );
    $mtimes = array('header' => array(), 'footer' => array());
  
    $url = get_bloginfo("wpurl");//we need the blogs url to assist in comparisons later
    
    //if nothing is registered then stop this madness
    if( count( $wp_scripts->registered ) === 0 || count( $assets['combine']['js'] ) === 0 )
      return false;
    
    $queue = $wp_scripts->queue;
    $wp_scripts->all_deps($queue);
    $to_do = $wp_scripts->to_do;
    
    //loop over the registered scripts for this page rquest
    foreach ($to_do as $key => $handle) 
    {
      //if the data is empty then die.
      if( !isset($wp_scripts->registered[$handle]) )
        continue;
      
      //store the src
      $src = $use = $wp_scripts->registered[$handle]->src;
      //check if the source has the full wp site url in it and remove it if it doest
      if( strstr($use, $url) )
        $use = str_replace( $url, "", $use );
      
      //store whether or not this file matches a file to combine
      $match = false;
      //loop the files list to combine
      foreach($assets['combine']['js'] as $js )
        //if the file is in the list
        if( strstr( $js, $use ) )
        {
          //we have a match, we'll continue below
          $match = true;
          break;
        }
      //file isn't in the combine list
      if( !$match )
        continue;      
      
      //store the handle and full file path for lookup later on compression
      $this->combined[$handle] = $js;
      /* used to pass up externals but now any file that gets included must be on the server to get found */
      //if( preg_match( "*(http://|https://)(?!".$_SERVER["SERVER_NAME"].")*", $src ) )
        //continue;
      
      //check for localize scripts data
      if( isset( $wp_scripts->registered[$handle]->extra['data'] ) )
        $localize[] = $wp_scripts->registered[$handle]->extra['data'];
      
      if( "development" === $compile_mode )
      {
        $dev_src = $this->local_path( $src );
        $mtime = filemtime( $dev_src );
      }
      
      //Footer scripts
      if( isset( $wp_scripts->registered[$handle]->extra['group'] ) )
      {
        if( "development" === $compile_mode )
          $mtimes['footer'][] = $mtime;
        $footer[$handle] = (object)array( 'src' => $src );
      }
      //header scripts
      else
      {
        if( "development" === $compile_mode )
          $mtimes['header'][] = $mtime;
        $header[$handle] = (object)array( 'src' => $src );
      }
      
      //remove this file from wp's registered script list and dequeue it
      unset( $wp_scripts->registered[$handle] );
      wp_dequeue_script( $handle );
    }
    //loop the queue'd scripts again and makre sure all the files are out of the queue for sure
    //not sure why but these sometimes get stuck in the queue still until this point...
    foreach ($wp_scripts->queue as $key => $handle)
      if ( isset( $header[$handle] ) || isset( $footer[$handle] ) )
        unset( $wp_scripts->queue[$key] );
    
    if( "header" === $force_combine )
    {
      $header = array_merge( $header, $footer );
      $footer = array();
    }
    elseif( "footer" === $force_combine )
    {
      $footer = array_merge( $header, $footer );
      $header = array();
    }
    
    //hash the scripts by name
    $footerHash = md5( implode( ',', array_keys( $footer ) ) . implode( ',', $mtimes['footer'] ) );
    $headerHash = md5( implode( ',', array_keys( $header ) ) . implode( ',', $mtimes['header'] ) );
    
    //give these files a full path
    $footerFile = $this->uploads['basedir'] . '/' . $this->upload_dir . '/'  . $footerHash . ".js";
    $headerFile = $this->uploads['basedir'] . '/' . $this->upload_dir . '/' . $headerHash . ".js";
    
    //make sure we have a place to put this file
    if( !is_dir( dirname( $footerFile ) ) )
      mkdir( dirname( $footerFile ), 0755, true );
    
    /* If the files don't exist them build them*/
    if( !is_file( $footerFile ) )
      $this->write_script_cache( $footerHash, $footer, true, $localize );
    
    else 
      $this->enqueue_packed_script( $footerHash, true, $localize );
    
    if( !is_file( $headerFile ) )
      $this->write_script_cache( $headerHash, $header, false, $localize );
    
    else 
      $this->enqueue_packed_script( $headerHash, false, $localize );
    
  }
  /**
   * Hooks to print footer scripts which calls our footer scripts
   */
  function print_footer_scripts(){}
  /**
   * Finds the files absolute path on the server
   * 
   * @since 1.0.0
   * 
   * @param string $path
   */
  function local_path( $path )
  {    
    $src = $path;
    $path = ( substr( $src, 0, 1) == "/" )? ABSPATH . substr( $src, 1 ): $src;
    $path = ( strstr( $path, get_bloginfo("wpurl") ) ) ? ABSPATH . str_replace( get_bloginfo("wpurl")."/", "", $src ) : $path; 
    
    return $path;  
  }
  /**
   * Combines the files and puts them into a cached file
   * 
   * @since 1.0.0
   * 
   * @param string $file The filename to write to
   * @param array $data List of file objects with src and path info
   * @param boolean $footer Whether or not to enqueue in the footer
   * @param array $localize any localize script data
   */
  private function write_script_cache( $file, $data, $footer = false, $localize = array() )
  {
    $cache = $file;
    $path = $this->uploads['basedir'] . '/' . $this->upload_dir . '/' . $file . ".js";
    $assets = get_option( 'mn_comine_assets', $this->default );//get the list of files we can compress/combine
    $compression = get_option( 'mn_compression_engine', $this->compression_engine );//get the list of files we can compress/combine
    //$implode just stores a nice comma separated list of files that were combined in this file
    $implode = array_keys( (array)$data );
    $implode = implode( ", ", $implode );
    //clear the cache file if for some reason it existed already; but it shouldn't
    if( is_file( $path ) )
      file_put_contents( $path, "" );

    //loop over our file data
    foreach( $data as $key => $f )
    {
      /* We're looking for our files on the server; converting url to location */
      $src = $this->local_path( $f->src );
      //$src = ( substr( $f->src, 0, 1) == "/" )? ABSPATH . $f->src : $f->src;
      //$src = ( strstr( $src, get_bloginfo("wpurl") ) ) ? ABSPATH . str_replace( get_bloginfo("wpurl")."/", "", $f->src ) : $src;    
      //can we find this file?
      if( !is_file( $src ) )
        continue;
      
      //get the file contents
      $content = file_get_contents( $src );
      //check if we're going to compress this or not
      if( in_array( str_replace("\\", "/", $src ), $assets['compress']['js'] ) )
      {
        $content = $this->{"_$compression"}($content);
      }
      file_put_contents( $path, $content . ";", FILE_APPEND | LOCK_EX );
    }
    //get the path of the newly created file
    if( !is_file( $path ) )
      return;
    
    else
    {
      $contents = file_get_contents( $path );
      if( empty( $contents ) )
        return;
      
      file_put_contents( $path, "/*$implode*/\n\n" . $contents );
    }
    $this->enqueue_packed_script( $cache, $footer, $localize );
  }
  /**
   * Sends javascript to google closure to minify
   */
  private function _google_closure($js)
  {    
    $ch = curl_init('http://closure-compiler.appspot.com/compile');

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    $opts = 'output_info=compiled_code&output_format=json&compilation_level=SIMPLE_OPTIMIZATIONS&js_code=' . urlencode($js);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $opts);
    $output = curl_exec($ch);
    
    $output = json_decode($output);
    
    //errors?
    if( null !== $output->errors || null !== $output->serverErrors || $error = curl_error($ch) )
    {
      $errors = array();
      
      if( !empty($error) )
        $errors[] = "Curl Error: $error";
      
      //Do some error handling to tell user what's up
      if( !empty($output->serverErrors) )
        foreach($output->serverErrors as $error )
          $errors[] = $error->error;
        
      if( !empty($output->errors) )
        foreach($output->serverErrors as $error )
          $errors[] = $error->error . " on line " . $error->lineno . " character " . $error->charno;
      
      $this->handleError( $errors );
      curl_close($ch);
      //return the original js. Uncompressed js is better than no js
      return $js;
    }
    
    curl_close($ch);
    return $output->compiledCode;
  }
  /**
   * Executes JSMin minification
   * 
   * @since 1.0.0
   * 
   * @param string $js
   */
  private function _js_min($js)
  {
    if( !class_exists('JSMin') )
      include( plugin_dir_path(__FILE__) . "jsmin.php" );
      
    return JSMin::minify($js);
  }
  /**
   * Empty wrapper for no minification
   * 
   * @since 1.0.0
   * 
   * @param string $js
   */
  private function _none($js)
  {
    return $js;
  }
  /**
   * Enqueue a cached script file
   * 
   * @since 1.0.0
   * 
   * @param string $file The filename to enqueue
   * @param boolean $footer Whether or not to enqueue in the footer
   * @param array $localize any localize script data
   */
  private function enqueue_packed_script( $file, $footer = false, $localize = array() )
  {
    $extra = "";
    //do localize first
    if( !empty( $localize ) )
      foreach( $localize as $s )
        $extra .= "$s\n";
    
    if( !empty( $extra ) && ( "footer" === get_option( 'mn_force_combine', $this->force_combine ) || $footer === false ) )
    {
      ?><script type="text/javascript" charset="utf-8"><?php echo $extra; ?></script><?php
    }
    
    $this->handle = 'mn_cache_' . uniqid();
    $path = $this->uploads['baseurl'] . '/' . $this->upload_dir . '/' . $file . ".js";
    
    wp_enqueue_script( $this->handle, $path, null, 0, $footer );
    
    /**
     * If we're unqueueing the header scripts then we need to 
     * print them out immediately
     */
    if( !$footer )
    {
      global $wp_scripts;
      if ( ! is_a( $wp_scripts, 'WP_Scripts' ) )
        $wp_scripts = new WP_Scripts();
      
      $wp_scripts->do_items(false, 0);
    }
  }
  /**
   * Action hook for wp_print_styles
   * 
   * @since 1.0.0
   */
  function wp_print_styles()
  {    
    global $wp_styles;
    
    $compile_mode = get_option( 'mn_compile_mode', $this->compile_mode );
    $mtimes = array('header' => array(), 'footer' => array());
    
    /* Make sure we have something to do here */
    if( count( $wp_styles->registered ) == 0 )
      return false;
    
    /* Let's get down to the styles we need for the page */
    $queue = $wp_styles->queue;
    $wp_styles->all_deps($queue);
    $to_do = $wp_styles->to_do;
    
    $styles = array();
    
    foreach ($to_do as $key => $handle) 
    {
      $src = $wp_styles->registered[$handle]->src;
      /* This is an external script. We may not be able to grab it. Let's not deal with it */
      if( preg_match( "*(http://)(?!".$_SERVER["SERVER_NAME"].")*", $src ) )
        continue;
      
      $styles[$handle] = (object)array( 'src' => $src );
      
      if( "development" === $compile_mode )
      {
        $dev_src = $this->local_path( $src );
        $mtimes[] = filemtime( $dev_src );
      }
      
      unset( $wp_styles->registered[$handle] );
      wp_dequeue_style( $handle );
    }
    /* We dequeued, but really make sure we're not going to get these styles in here again */
    foreach ($wp_styles->queue as $key => $handle)
      if ( isset( $styles[$handle] ) )
        unset( $wp_styles->queue[$key] );
    
    $keys = implode( ',', array_keys( $styles ) ) . implode( ',', $mtimes );
    $hash = md5( $keys );
    $file = $this->uploads['basedir'] . '/' . $this->upload_dir . '/' . $hash . ".css";
    
    //make sure we have a place to put this in case we wipe out the whole cache file to do a quick clear
    if( !is_dir( dirname( $file ) ) )
      mkdir( dirname( $file ), 0755, true );
  
    if( !is_file( $file ) || !NO_CACHE )
      $this->write_style_cache( $hash, $styles );
    
    else 
      $this->enqueue_packed_style( $hash );
    
  }
  /**
   * Combines the files and puts them into a cached file
   * 
   * @since 1.0.0
   * 
   * @param string $file The filename to write to
   * @param array $data List of file objects with src and path info
   * @param boolean $footer Whether or not to enqueue in the footer
   */
  function write_style_cache( $file, $data )
  {
    $cache = $file;
    $path = $this->uploads['basedir'] . '/' . $this->upload_dir . '/' . $file . ".css";
    
    $implode = array_keys( (array)$data );
    $implode = implode( ", ", $implode );
    //clear the cache file if for some reason it existed already; but it shouldn't
    if( is_file( $path ) )
      file_put_contents( $path, "" );
    
    foreach( $data as $key => $info )
    {
      $f = $info;
      /* We're looking for our files on the server; converting url to location */
      //$src = ( substr( $f->src, 0, 1) == "/" ) ? ABSPATH . substr( $f->src, 1 ) : $f->src;
      //$src = ( strstr( $src, get_bloginfo("wpurl") ) ) ? ABSPATH . str_replace( get_bloginfo("wpurl")."/", "", $f->src ) : $src;
      $src = $this->local_path( $f->src );
      
      if( !is_file( $src ) )
        continue;
            
      $content = file_get_contents( $src );
      //if( NO_COMPRESS_CACHED_SCRIPTS )
      //{
        //echo "SRC: $src<br/>";
      $content = $this->compress_css($content, $path, $src);
      //}
      file_put_contents( $path, "/*$key*/\n$content\n\n", FILE_APPEND | LOCK_EX );
    }
    //get the path of the newly created file
    if( !is_file( $path ) )
      return;
    
    else
    {
      $contents = file_get_contents( $path );
      if( empty( $contents ) )
        return;
      
      file_put_contents( $path, "/*$implode*/\n\n" . $contents );
    }
    $this->enqueue_packed_style( $cache );
  }
  /**
   * Enqueue a cached script file
   * 
   * @since 1.0.0
   * 
   * @param string $file The filename to enqueue
   */
  function enqueue_packed_style( $file )
  {
    $handle = 'mn_cache_' . uniqid();
    $path = $this->uploads['baseurl'] . '/' . $this->upload_dir . '/' . $file . ".css";
    
    wp_enqueue_style( $handle, $path, null, 0);
    
    global $wp_styles;
    if ( ! is_a( $wp_styles, 'WP_Styles' ) )
      $wp_styles = new WP_Styles();
  
    $wp_styles->do_items();
  }
  /**
   * Compresses css
   * 
   * @since 1.0.0
   * 
   * @param string $css
   * @param string $path
   * @param string $src
   */
  function compress_css($css, $path, $src) 
  {
    //fix urls in the css before handling imports
    $css = $this->url_css($css, $path, $src);
    //find any imports
    $css = $this->import_css($css, $path, $src);
    
    // remove comments, tabs, spaces, newlines, etc.
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);

    return $css;
  }
  /**
   * Handles css url paths
   * 
   * @since 1.0.0
   * 
   * @param string $css
   * @param string $path
   * @param string $src
   */
  function url_css($css, $path, $src)
  {
    $this->dir = dirname($src).'/';
    $css = preg_replace_callback(
      '|url\(\'?"?([^\"\')]*)\'?"?\)|',
      array( $this, 'url_css_callback' ),
      $css
    );
    return $css;
  }
  /**
   * callback for replacing found css urls
   * 
   * @param array $matches
   * 
   * @since 1.0.0
   */
  function url_css_callback($matches)
  {
    $path = $this->dir . $matches[1];
    if( strstr( $path, "./" ) )
      $path = $this->canonicalize( $path );
    
    $path = str_replace( ABSPATH, "/", $path );
    
    return "url(\"$path\")";
  }
  /**
   * Handle css @import
   * 
   * @since 1.0.0
   * 
   * @param string $css
   * @param string $path
   * @param string $src
   */
  function import_css($css, $path, $src)
  {
    //Find import statements
    if( preg_match_all('/@import\s+(.*)/', $css, $matches) )
      foreach( $matches[1] as $match )
        $css_imports[] = $match;
    
    //if we're importing into this css file then we need to find 
    if( !empty($css_imports) )
      foreach($css_imports as $file )
      {
        $file = preg_replace( '/[;\'"]/', '', $file );
        $file = dirname($src) . '/' . $file;
        if( strstr( $src, "./" ) )
          $file = $this->canonicalize( $file ); 
        
        //get the imported file contents and put it where the @import statement was
        if( $content = @file_get_contents( $file ) ) 
        {
          //run the content through the same filters in case we have nested imports / urls
          $content = $this->url_css($content, $path, $file);
          $content = $this->import_css($content, $path, $file);
          //finally replace the import statement with the file's contents
          $css = preg_replace( '/@import\s+.*/', "\n" . $content . "\n", $css, 1 );
        }
      }
    return $css;
  }
  protected function handleError( $errors )
  {
    //do something here with errors
    var_dump($errors);
    exit;
  }
  /**
   * find a css url realpath
   * 
   * @param string $address
   */
  function canonicalize($address)
  {
    $address = explode('/', $address);
    $keys = array_keys($address, '..');

    foreach($keys AS $keypos => $key)
      array_splice($address, $key - ($keypos * 2 + 1), 2);

    $address = implode('/', $address);
    $address = str_replace('./', '', $address);
    
    return $address;
  }
}
/**
 * Class to override php RecursiveRegexIterator for finding file extensions
 */
abstract class FilesystemRegexFilter extends RecursiveRegexIterator {
    protected $regex;
    public function __construct(RecursiveIterator $it, $regex) {
        $this->regex = $regex;
        parent::__construct($it, $regex);
    }
}
/**
 * Filter file extensions found by regex
 */
class FilenameFilter extends FilesystemRegexFilter {
    // Filter files against the regex
    public function accept() {
        return ( ! $this->isFile() || preg_match($this->regex, $this->getFilename()));
    }
}
/**
 * Filter out folders by name in regex
 */
class DirnameFilter extends FilesystemRegexFilter {
    // Filter directories against the regex
    public function accept() {
        return ( ! $this->isDir() || preg_match($this->regex, $this->getFilename()));
    }
}