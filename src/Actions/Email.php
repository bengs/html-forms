<?php

namespace HTML_Forms\Actions;

use HTML_Forms\Form;
use HTML_Forms\Submission;

class Email extends Action {

   public $type = 'email';
   public $label = 'Send Email';

   public function __construct() {
       $this->label = __( 'Send Email', 'html-forms' );
   }

   /**
   * @return array
   */
   private function get_default_settings() {
       $defaults = array(
          'from' => get_option( 'admin_email' ),
          'to' => get_option( 'admin_email' ),
          'subject' => '',
          'message' => '',
          'headers' => '', 
          'content_type' => 'text/html',
       );
       return $defaults;
   }

   /**
   * @param array $settings
   * @param string|int $index
   */ 
   public function page_settings( $settings, $index ) {
       $settings = array_merge( $this->get_default_settings(), $settings );
       ?>
       <span class="hf-action-summary"><?php printf( 'From %s. To %s.', $settings['from'], $settings['to']  ); ?></span>
       <input type="hidden" name="form[settings][actions][<?php echo $index; ?>][type]" value="<?php echo $this->type; ?>" />
       <table class="form-table">
           <tr>
               <th><label><?php echo __( 'From', 'html-forms' ); ?> <span class="hf-required">*</span></label></th>
               <td>
                   <input name="form[settings][actions][<?php echo $index; ?>][from]" value="<?php echo esc_attr( $settings['from'] ); ?>" type="text" class="regular-text" placeholder="jane@email.com" required />
               </td>
           </tr>
           <tr>
               <th><label><?php echo __( 'To', 'html-forms' ); ?> <span class="hf-required">*</span></label></th>
               <td>
                   <input name="form[settings][actions][<?php echo $index; ?>][to]" value="<?php echo esc_attr( $settings['to'] ); ?>" type="text" class="regular-text" placeholder="john@email.com" required />
               </td>
           </tr>
           <tr>
               <th><label><?php echo __( 'Subject', 'html-forms' ); ?></label></th>
               <td>
                   <input name="form[settings][actions][<?php echo $index; ?>][subject]" value="<?php echo esc_attr( $settings['subject'] ); ?>" type="text" class="regular-text" placeholder="<?php echo esc_attr( __( 'Your email subject', 'html-forms' ) ); ?>" />
               </td>
           </tr>
            
           <tr>
               <th><label><?php echo __( 'Message', 'html-forms' ); ?> <span class="hf-required">*</span></label></th>
               <td>
                   <textarea name="form[settings][actions][<?php echo $index; ?>][message]" rows="8" class="widefat" placeholder="<?php echo esc_attr( __( 'Your email message', 'html-forms' ) ); ?>" required><?php echo esc_textarea( $settings['message'] ); ?></textarea>
                   <p class="help"><?php _e( 'You can use the following variables (in all fields): ', 'html-forms' ); ?><span class="hf-field-names"></span></p>                 
               </td>
           </tr>

           <tr>
               <th><label><?php echo __( 'Content Type', 'html-forms' ); ?></label></th>
               <td>
                   <select name="form[settings][actions][<?php echo $index; ?>][content_type]" required>
                      <option <?php selected( $settings['content_type'], 'text/html' ); ?>>text/plain</option>
                      <option <?php selected( $settings['content_type'], 'text/html' ); ?>>text/html</option>
                   </select>
               </td>
           </tr>
        
           <tr>
               <th><label><?php echo __( 'Additional headers', 'html-forms' ); ?></label></th>
               <td>
                   <textarea name="form[settings][actions][<?php echo $index; ?>][headers]" rows="4" class="widefat" placeholder="<?php echo esc_attr( 'Reply-To: [NAME] <[EMAIL]>' ); ?>"><?php echo esc_textarea( $settings['headers'] ); ?></textarea>
               </td>
           </tr>
       </table>
        <?php
   }

    /**
     * Processes this action
     *
     * @param array $settings
     * @param Submission $submission
     * @param Form $form
     */
   public function process( array $settings, Submission $submission, Form $form ) {
       if( empty( $settings['to'] ) || empty( $settings['message'] ) ) {
           return;
       }

       $settings = array_merge( $this->get_default_settings(), $settings );

       $to = hf_template( $settings['to'], $submission->data );
       $subject = ! empty( $settings['subject'] ) ? hf_template( $settings['subject'], $submission->data ) : '';
       $message = hf_template( $settings['message'], $submission->data );
       $headers = explode( PHP_EOL, $settings['headers'] );
       $html_email = $settings['content_type'] === 'text/html';

       if( $html_email ) {
          $headers[] = 'Content-Type: text/html';
          $message = nl2br( $message );
       }

       if( ! empty( $settings['from'] ) ) {
           $from = hf_template($settings['from'], $submission->data);
           $headers[] = sprintf( 'From: %s', $from );
       }

       wp_mail( $to, $subject, $message, $headers );
   }
}
