<?php if ( $tickets ): ?>
  <div class="notice notice-info is-dismissible">
    <p><?php _e( 'New messages are displayed at the top of the list', 'wc-tickets' ); ?>.</p>
  </div>

  <div class="wc-tickets-list">
	  <?php foreach ( $tickets as $key => $tick ): ?>
		  <?php
		  $ticket  = maybe_unserialize( maybe_unserialize( $tick->meta_value ) );
		  $user_id = $ticket['author'];
		  ?>
        <div class="comment">
          <div class="comment-avatar"
               style="background-image: url(<?php echo esc_url( get_avatar_url( $user_id ) ) ?>);">
          </div>
          <div class="comment-author"><?php echo get_user_meta( $user_id, 'first_name', 1 ) ?>
            &nbsp;<?php echo get_user_meta( $user_id, 'last_name', 1 ) ?></div>
          <div class="comment-text">
			  <?php echo $ticket['message'] ?>
            <div class="comment-date"><?php echo date_i18n( 'd.m.Y H:i', $ticket['date'] ) ?></div>
          </div>

			<?php if ( ! empty( $ticket['image'] ) ): ?>
              <div class="comment-atachments">
                <b><?php _e( 'Attachments', 'wc-tickets' ); ?>:</b>
                <a
                  href="<?php echo esc_url( wp_get_attachment_image_url( $ticket['image'], 'full' ) ) ?>?TB_iframe=true"
                  class="thickbox"
                  title="<?php echo esc_attr( basename( wp_get_attachment_image_url( $ticket['image'], 'full' ) ) ) ?>"><?php echo basename( wp_get_attachment_image_url( $ticket['image'], 'full' ) ) ?></a>
              </div>
			<?php endif ?>

        </div>
	  <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if ( $post->post_status != 'closed' ): ?>
  <p>
    <label for="wc-tickets-reply"><?php _e( 'Your Answer', 'wc-tickets' ); ?></label>
	  <?php
	  wp_editor( '', 'wc-tickets-reply', array(
		  'wpautop'          => 1,
		  'media_buttons'    => 1,
		  'textarea_name'    => 'wc-tickets-reply',
		  'textarea_rows'    => 4,
		  'tabindex'         => null,
		  'editor_css'       => '',
		  'editor_class'     => 'wc-tickets-reply',
		  'teeny'            => 0,
		  'dfw'              => 0,
		  'tinymce'          => 0,
		  'quicktags'        => 1,
		  'drag_drop_upload' => false
	  ) );
	  ?>
  </p>
  <p><input type="checkbox" name="wc-tickets-close" value="1" id="wc-tickets-close"> <label
      for="wc-tickets-close"><?php _e( 'Close ticket', 'wc-tickets' ); ?></label></p>
  <p>
    <button type="submit" class="button button-primary button-large"><?php _e( 'Replay', 'wc-tickets' ); ?></button>
  </p>
<?php else: ?>
  <p><?php _e( 'This ticket is closed.', 'wc-tickets' ); ?></p>
<?php endif; ?>

<style>
  #TB_window {
    max-width: 400px;
    margin-left: -200px !important;
    height: auto !important;
  }

  #TB_window img#TB_Image {
    width: 100%;
    height: auto;
    margin: 0;
    padding: 0;
    border: none;
  }

  #TB_window #TB_closeWindow {
    position: absolute;
    top: -30px;
    right: -40px;

  }

  #TB_window .tb-close-icon:before {
    content: "\f158";
    font: normal 27px/1em dashicons;
    speak: none;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    color: #fff;
  }

  #TB_caption {
    height: 25px;
    padding: 18px 30px 10px 25px;
    float: none;
    line-height: 1;
    font-weight: bold;
    text-align: center;
    font-size: 15px;
    background: #fff;
  }
</style>
