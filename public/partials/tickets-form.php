<?php
/**
 * This file contains the xml form of sending a ticket
 *
 *
 * @link       http://profglobal.pro/
 * @since      1.0.0
 *
 * @package    Wc_Tickets
 * @subpackage Wc_Tickets/public/partials
 */

global $wpdb;

$paged = ( ! empty( $_GET['index'] ) ) ? intval( $_GET['index'] ) : 1;
$query = new WP_Query( array(
	'post_type'   => $this->post_type,
	'author' => $current_user->ID,
	'post_status' => array( 'publish', 'closed' ),
	'paged'       => $paged
) );
?>

<div class="tab-title active"><?php _e( 'Complaints and suggestions', 'wc-tickets' ); ?></div>

<?php if ( $query->have_posts() ) : ?>
  <div class="wct-f-list">
   <div class="wctf-overlay" style="display: none;"><img src="<?php echo esc_url( plugins_url( 'wc-tickets/public/img/preloader.svg' ) ) ?>"
                                  alt="preloader" ></div>
    <div class="wctf-title"><?php _e( 'List of references', 'wc-tickets' ); ?></div>
	  <?php
	  $count = 1;
	  while ( $query->have_posts() ) : $query->the_post(); ?>
        <div class="wctf-head">
          <button class="wctf-collapse" data-toggle="collapse"
                  data-target="#wctf-<?php echo esc_attr( get_the_ID() ) ?>"></button>
          <span class="count">[ID:<?php echo esc_attr( get_the_ID() ) ?>] </span>
          <span><?php the_title() ?></span>
			<?php if ( get_post_status() == 'publish' ): ?>
              <button class="wctf-close"
                      data-action="close-ticket"
                      data-ticket="<?php echo esc_attr( get_the_ID() ) ?>"
                      data-toggle="tooltip"
                      data-nonce="<?php echo esc_attr( wp_create_nonce() ) ?>"
                      title="<?php esc_html_e( 'Click here to close the correspondence now or it will be closed automatically after a while.', 'wc-tickets' ); ?>"><?php _e( 'The question is settled', 'wc-tickets' ); ?></button>
			<?php elseif ( get_post_status() == 'closed' ): ?>
              <button class="wctf-close closed"><?php _e( 'Closed', 'wc-tickets' ); ?></button>
			<?php endif; ?>


        </div>
        <div class="wctf-body collapse" id="wctf-<?php echo esc_attr( get_the_ID() ) ?>">
			<?php

			$tickets = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id='%s' AND meta_key='%s' ORDER BY meta_id DESC ", get_the_ID(), '_wc_ticket' ) );
			if ( $tickets ) {
				foreach ( $tickets as $key => $tick ): ?>
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
				<?php endforeach;
			} ?>
        </div>
		  <?php
		  $count ++;
	  endwhile; ?>
	  <?php
	  wp_reset_query();
	  global $post;
	  echo paginate_links( array(
		  'base'    => esc_url( get_the_permalink( $post->ID ) . 'tickets/?index=%_%' ),
		  'format'  => '%#%',
		  'current' => $paged,
		  'total'   => $query->max_num_pages
	  ) ); ?>
  </div>
<?php endif; ?>

<form name="ticketForm" id="ticket-form" enctype="multipart/form-data" class="ticket-form">

	<?php wp_nonce_field() ?>
  <input type="hidden" name="action" value="send_ticket">

  <div class="form-row">
    <label for="wct-parent-ticket"><?php _e( 'New ticket or continue', 'wc-tickets' ); ?></label>
    <select name="parent" id="wct-parent-ticket">
      <option value=""><?php _e( 'New Ticket', 'wc-tickets' ); ?></option>
		<?php
		wp_reset_query();
		$query = new WP_Query( array(
			'post_type'      => $this->post_type,
			'post_author'    => $current_user->ID,
			'post_status'    => 'publish',
			'posts_per_page' => - 1
		) ) ?>
		<?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>
          <option value="<?php echo esc_attr( get_the_ID() ) ?>">[ID:<?php echo esc_attr( get_the_ID() ) ?>] - <?php the_title() ?></option>
		<?php endwhile; ?>
		<?php endif; ?>
    </select>
  </div>
  <div class="form-row">
    <label for="wct-subject"><?php _e( 'Topic Title', 'wc-tickets' ); ?></label>
    <input type="text" name="subject" id="wct-subject" required>
  </div>
	<?php if ( empty( $current_user->first_name ) ): ?>
      <div class="form-row">
        <label for="wct-user_name"><?php _e( 'Your name', 'wc-tickets' ); ?></label>
        <input type="text" name="user_name" id="wct-user_name"
               value="<?php echo esc_attr( $current_user->first_name ) ?>"
               required>
      </div>
	<?php endif; ?>
  <div class="form-row">
    <label for="ticket-editor"><?php _e( 'Your complaint or suggestion', 'wc-tickets' ); ?></label>
	  <?php wp_editor( '', 'ticket-editor', array(
		  'wpautop'          => 1,
		  'media_buttons'    => 0,
		  'textarea_name'    => 'ticket-editor', //нужно указывать!
		  'textarea_rows'    => 8,
		  'tabindex'         => null,
		  'editor_css'       => '',
		  'editor_class'     => 'ticket-editor',
		  'teeny'            => 0,
		  'dfw'              => 0,
		  'tinymce'          => 0,
		  'quicktags'        => 1,
		  'drag_drop_upload' => true
	  ) ); ?>
  </div>
  <div class="form-row">
    <div class="media-upload">
      <label class="btn-grey">
        <input type="file" name="add-photo" id="ticket-file-select">
        <span class="icon icon-camera"></span> <?php esc_html_e( 'Attach a photo', 'wc-tickets' ); ?></label>
    </div>
  </div>
  <div class="form-row">
    <button type="submit">
		<?php esc_attr_e( 'Send', 'wc-tickets' ); ?>
      <img src="<?php echo esc_url( plugins_url( 'wc-tickets/public/img/preloader.svg' ) ) ?>" class="preloader"
           alt="preloader" width="25"></button>
  </div>
  <div class="message-box"></div>
</form>

