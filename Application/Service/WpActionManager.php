<?php
namespace WPOptimizeByxTraffic\Application\Service;

use WPOptimizeByxTraffic\Application\Service\PepVN_Data
	, WPOptimizeByxTraffic\Application\Service\PepVN_Cache
	, WPOptimizeByxTraffic\Application\Service\PepVN_CacheSimpleFile
	, WpPepVN\DependencyInjectionInterface
	, WpPepVN\DependencyInjection
	, WpPepVN\System
;

class WpActionManager 
{
	public $di = false;
	
    public function __construct(DependencyInjection $di) 
    {
		$this->di = $di;
		
		add_action('comment_post', array($this, 'comment_post'), WP_PEPVN_PRIORITY_LAST, 2);
		add_action('wp_insert_comment', array($this, 'wp_insert_comment'), WP_PEPVN_PRIORITY_LAST, 2);
		add_action('wp_set_comment_status', array($this, 'wp_set_comment_status'), WP_PEPVN_PRIORITY_LAST, 2);
		add_action('delete_comment', array($this, 'delete_comment'), WP_PEPVN_PRIORITY_FIRST, 1);
		add_action('deleted_comment', array($this, 'deleted_comment'), WP_PEPVN_PRIORITY_FIRST, 1);
		add_action('edit_comment', array($this, 'edit_comment'), WP_PEPVN_PRIORITY_LAST, 1);
		add_action('transition_comment_status', array($this, 'transition_comment_status'), WP_PEPVN_PRIORITY_LAST, 3);
		
		add_action('transition_post_status', array($this, 'transition_post_status'), WP_PEPVN_PRIORITY_LAST, 3);
		add_action('edit_post', array($this, 'edit_post'), WP_PEPVN_PRIORITY_FIRST, 2);
		add_action('save_post', array($this, 'save_post'), WP_PEPVN_PRIORITY_FIRST, 3);
		add_action('publish_future_post', array($this, 'publish_future_post'), WP_PEPVN_PRIORITY_FIRST, 1);
		add_action('after_delete_post', array($this, 'after_delete_post'), WP_PEPVN_PRIORITY_FIRST, 1);
		
		add_action('activated_plugin', array($this, 'activated_plugin'), WP_PEPVN_PRIORITY_LAST, 2);
		add_action('deactivated_plugin', array($this, 'deactivated_plugin'), WP_PEPVN_PRIORITY_LAST, 2);
		
		add_action('switch_theme', array($this, 'switch_theme'), WP_PEPVN_PRIORITY_LAST, 2);
		
	}
	
	private function _cleanCache($data_type = ',common,')
	{
		$cacheManager = $this->di->getShared('cacheManager');
		
		$cacheManager->registerCleanCache($data_type);
	}
	
	/*
		Runs just after a comment is saved in the database. 
		Action function arguments: comment ID, approval status ("spam", or 0/1 for disapproved/approved).
		http://codex.wordpress.org/Plugin_API/Action_Reference/comment_post
	*/
	public function comment_post(
		$comment_ID	//The comment that is created.
		, $comment_approved //$comment_approved
	) {
		$this->_cleanCache();
	}
	
	/*
		Runs whenever a comment is created.
		http://codex.wordpress.org/Plugin_API/Action_Reference/wp_insert_comment
		@param int $id      The comment ID.
		@param obj $comment Comment object.
	*/
	public function wp_insert_comment(
		$id
		, $comment
	) {
		$this->_cleanCache();
		
	}
	
	/*
		Runs when the status of a comment changes. 
		Action function arguments: comment ID, status string indicating the new status ("delete", "approve", "spam", "hold").
		@param int         $comment_id     Comment ID.
		@param string|bool $comment_status Current comment status. Possible values include
			'hold', 'approve', 'spam', 'trash', or false.
	*/
	public function wp_set_comment_status(
		$comment_id
		, $comment_status
	) {
		$this->_cleanCache();
	}
	
	/*
		Runs just before a comment is deleted. Action function arguments: comment ID.
		Fires immediately before a comment is deleted from the database.
		@param int $comment_id The comment ID.
	*/
	public function delete_comment(
		$comment_id
	) {
		$this->_cleanCache();
	}
	
	/*
		Runs just after a comment is deleted. Action function arguments: comment ID.
		Fires immediately after a comment is deleted from the database.
		@param int $comment_id The comment ID.
	*/
	public function deleted_comment(
		$comment_id
	) {
		$this->_cleanCache();
	}
	
	/*
		Fires immediately after a comment is updated in the database.
		The hook also fires immediately before comment status transition hooks are fired.
		@param int $comment_ID The comment ID.
	*/
	public function edit_comment(
		$comment_ID
	) {
		$this->_cleanCache();
	}
	
	/*
		Fires when the comment status is in transition.
		@param int|string $new_status The new comment status.
		@param int|string $old_status The old comment status.
		@param object     $comment    The comment data.
	*/
	public function transition_comment_status(
		$new_status
		, $old_status
		, $comment
	) {
		$this->_cleanCache();
	}
	
	/*
		Fires when a post is transitioned from one status to another.
		@param string  $new_status New post status.
		@param string  $old_status Old post status.
		@param WP_Post $post       Post object.
	*/
	public function transition_post_status(
		$new_status
		, $old_status
		, $post
	) {
		$hook = $this->di->getShared('hook');
		if($hook->has_action('transition_post_status')) {
			$hook->do_action('transition_post_status', array(
				'new_status' => $new_status
				, 'old_status' => $old_status
				, 'post' => $post
			));
		}
		
	}
	
	/*
		Fires once an existing post has been updated.
		@param int     $post_ID Post ID.
		@param WP_Post $post    Post object.
	*/
	public function edit_post(
		$post_ID
		, $post
	) {
		
	}
	
	/*
		save_post is an action triggered whenever a post or page is created or updated, which could be from an import
			, post/page edit form, xmlrpc, or post by email.
		The data for the post is stored in $_POST, $_GET or the global $post_data, depending on how the post was edited. 
		For example, quick edits use $_POST.
		Since this action is triggered right after the post has been saved
			, you can easily access this post object by using get_post($post_id)
		Fires once a post has been saved.
		@param int     $post_ID Post ID.
		@param WP_Post $post    Post object.
		@param bool    $update  Whether this is an existing post being updated or not.
	*/
	public function save_post(
		$post_ID
		, $post
		, $update
	) {
		$wpExtend = $this->di->getShared('wpExtend');
		$hook = $this->di->getShared('hook');
		
		// If this is just a revision/autosave, don't clean cache
		if(false === $wpExtend->isRequestIsAutoSavePosts()) {
			if ( false === wp_is_post_revision( $post_ID ) ) {
				if ( false === wp_is_post_autosave( $post_ID ) ) {
					
					if($hook->has_action('save_post_primary')) {
						$hook->do_action('save_post_primary', array(
							'post_ID' => $post_ID
							, 'post' => $post
							, 'update' => $update
						));
					}
					
					
					if(
						('publish' === get_post_status($post_ID))
					) {
						if($hook->has_action('save_post_publish')) {
							$hook->do_action('save_post_publish', array(
								'post_ID' => $post_ID
								, 'post' => $post
								, 'update' => $update
							));
						}
						
						$this->_cleanCache();
						
					}
					
					
				}
			}
		}
		
	}
	
	/*
		Fires once an existing post has been updated.
		@param int     $post_ID Post ID.
		@param WP_Post $post    Post object.
	*/
	public function publish_future_post(
		$post_id
	) {
		$this->_cleanCache();
	}

	/**
	* Fires after a post is deleted, at the conclusion of wp_delete_post().
	*
	* @since 3.2.0
	*
	* @see wp_delete_post()
	*
	* @param int $postid Post ID.
	*/
	
	public function after_delete_post(
		$postid
	) {
		$postid = (int)$postid;
		$hook = $this->di->getShared('hook');
		if($hook->has_action('after_delete_post')) {
			$hook->do_action('after_delete_post', $postid);
		}
		
		$this->_cleanCache();
	}
	
	/**
	* Fires after a plugin has been activated.
	*
	* If a plugin is silently activated (such as during an update),
	* this hook does not fire.
	*
	* @since 2.9.0
	*
	* @param string $plugin       Plugin path to main plugin file with plugin data.
	* @param bool   $network_wide Whether to enable the plugin for all sites in the network
	* 		or just the current site. Multisite only. Default is false.
	*/
	public function activated_plugin(
		$plugin
		, $network_wide
	) {
		$this->_cleanCache();
	}
	
	/**
	* Fires after a plugin has been deactivated.
	*
	* If a plugin is silently deactivated (such as during an update),
	* this hook does not fire.
	*
	* @since 2.9.0
	*
	* @param string $plugin               Plugin basename.
	* @param bool   $network_deactivating Whether the plugin is deactivated for all sites in the network
	* 		or just the current site. Multisite only. Default false.
	*/
	public function deactivated_plugin(
		$plugin
		, $network_deactivating
	) {
		$this->_cleanCache();
	}
	
	/**
	* Fires after the theme is switched.
	*
	* @since 1.5.0
	*
	* @param string   $new_name  Name of the new theme.
	* @param WP_Theme $new_theme WP_Theme instance of the new theme.
	*/
	public function switch_theme(
		$new_name
		, $new_theme 
	) {
		$this->_cleanCache();
	}
	
	
}