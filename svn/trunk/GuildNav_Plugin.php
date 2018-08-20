<?php


include_once('GuildNav_LifeCycle.php');
include_once('GuildNav_Explorer.php');

class GuildNav_Plugin extends GuildNav_LifeCycle {

  /**
   * See: http://plugin.michael-simpson.com/?page_id=31
   * @return array of option meta data.
   */
  public function getOptionMetaData() {
      //  http://plugin.michael-simpson.com/?page_id=31
      return array(
          //'_version' => array('Installed Version'), // Leave this one commented-out. Uncomment to test upgrades.
          'SiteCode' => array(__('Site Code', 'guild-nav')),
          // 'Theme' => array(__('Theme', 'guild-nav'), 'dark', 'light'),
          'GuildServerUrl' => array(__('Guild server (test-only)', 'guild-nav'), ''),
      );
  }

  protected function initOptions() {
      $options = $this->getOptionMetaData();
      if (!empty($options)) {
          foreach ($options as $key => $arr) {
              if (is_array($arr) && count($arr) > 1) {
                  $this->addOption($key, $arr[1]);
              }
          }
      }
  }

  public function getPluginDisplayName() {
      return 'Guild Nav';
  }

  protected function getMainPluginFileName() {
      return 'guild-nav.php';
  }

  /**
   * See: http://plugin.michael-simpson.com/?page_id=101
   * Called by install() to create any database tables if needed.
   * Best Practice:
   * (1) Prefix all table names with $wpdb->prefix
   * (2) make table names lower case only
   * @return void
   */
  protected function installDatabaseTables() {
      //        global $wpdb;
      //        $tableName = $this->prefixTableName('mytable');
      //        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
      //            `id` INTEGER NOT NULL");
  }

  /**
   * See: http://plugin.michael-simpson.com/?page_id=101
   * Drop plugin-created tables on uninstall.
   * @return void
   */
  protected function unInstallDatabaseTables() {
      //        global $wpdb;
      //        $tableName = $this->prefixTableName('mytable');
      //        $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
  }


  /**
   * Perform actions when upgrading from version X to version Y
   * See: http://plugin.michael-simpson.com/?page_id=35
   * @return void
   */
  public function upgrade() {
  }

  public function addActionsAndFilters() {
      // Add options administration page
      // http://plugin.michael-simpson.com/?page_id=47
      add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

      // Example adding a script & style just for the options administration page
      // http://plugin.michael-simpson.com/?page_id=47
      //        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
      //            wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
      //            wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
      //        }


      // Add Actions & Filters
      // http://plugin.michael-simpson.com/?page_id=37

      add_action( 'wp_head', array(&$this, 'addGuildPageHeader'));
      // add_filter( 'post_class', array(&$this, 'addGuildPostClass'));


      // Adding scripts & styles to all pages
      // Examples:
      //        wp_enqueue_script('jquery');
      //        wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
      //        wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));


      // Register short codes
      // http://plugin.michael-simpson.com/?page_id=39


      // Register AJAX hooks
      // http://plugin.michael-simpson.com/?page_id=41

      // Ensure pages can be configured with categories and tags
      add_action( 'init', array(&$this, 'add_taxonomies_to_pages'));

      $prefix = is_network_admin() ? 'network_admin_' : '';
      $plugin_file =  plugin_basename($this->getPluginDir() . DIRECTORY_SEPARATOR . $this->getMainPluginFileName()); //plugin_basename( $this->getMainPluginFileName() );
      $this->guildLog('Adding filter ' . "{$prefix}plugin_action_links_{$plugin_file}");
      add_filter( "{$prefix}plugin_action_links_{$plugin_file}", array(&$this, 'onActionLinks'));
  }

  public function onActionLinks( $links ) {
    $this->guildLog('onActionLinks ' . admin_url( 'options-general.php?page=GuildNav_PluginSettings' ));
    $mylinks = array('<a href="' . admin_url( 'options-general.php?page=GuildNav_PluginSettings' ) . '">Settings</a>');
    return array_merge( $links, $mylinks );
  }

  public function add_taxonomies_to_pages() {
    register_taxonomy_for_object_type( 'post_tag', 'page' );
    register_taxonomy_for_object_type( 'category', 'page' );
  }

  public function addGuildPageHeader() {
    global $page;
    global $post;
    if ($post) {
      $postId = $post->ID;
      $categories = get_the_category();
      foreach($categories as $category) {
        echo '<meta name="guild-category" content="' . $category->name . '" />' . "\n";
      }
      $tags = get_the_tags();
      foreach($tags as $tag) {
        echo '<meta name="guild-tag" content="' . $tag->name . '" />' . "\n";
      }
    }

    $siteCode = $this->getOption('SiteCode');
    if ($siteCode) {
      $serverUrl = 'https://guild.network/e1/embed-nav.js';
      $override = $this->getOption('GuildServerUrl', '');
      if (isset($override) && trim($override) !== '') {
        $serverUrl = trim($override);
      }
      echo "\n" . '<script defer src="' . $serverUrl . '"></script>' . "\n";
      echo '<script>';
      echo '  window.guild = { site: \'' . $siteCode . '\' };' . "\n";
      echo '</script>' . "\n";  
    }
  }

  private function getPostTitlesByCategory($categoryId) {
    $args = array(
      'posts_per_page'   => 3,
      'category'         => $categoryId,
      'orderby'          => 'date',
      'order'            => 'DESC'
    );
    $posts = get_posts($args);
    $result = array();
    foreach ($posts as $post) {
      $result[] = get_the_title($post->ID);
    }
    return $result;
  }

  private function getPostTitlesByTag($tagId) {
    $args = array(
      'posts_per_page'   => 3,
      'tag_id'           => $tagId,
      'orderby'          => 'date',
      'order'            => 'DESC'
    );
    $posts = get_posts($args);
    $result = array();
    foreach ($posts as $post) {
      $result[] = get_the_title($post->ID);
    }
    return $result;
  }

  private function echoExclusiveTitles($titles) {
    echo 'exclusiveTitles: [';
    foreach ($titles as $title) {
      echo '\'' . $title . '\', ';
    }
    echo ']';
  }

  private function get_tag_ID($tag_name) {
    $tag = get_term_by('name', $tag_name, 'post_tag');
    if ($tag) {
      return $tag->term_id;
    } else {
      return 0;
    }
  }

  private function get_top_parent_page_id() { 
    global $post; 
    if ($post->ancestors) { 
      return end($post->ancestors); 
    } else { 
      return null; 
    } 
  }
}
