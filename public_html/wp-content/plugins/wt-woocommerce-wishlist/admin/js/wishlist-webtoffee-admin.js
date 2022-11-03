(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
	 *
	 * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
	 *
	 * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    
    $(document).ready(function(){
        
        $("select.wt_button_type").change(function(){
            if('text' == ($(this).children("option:selected").val()) ) {
                $('.hide_text_element').closest( 'tr' ).show();
                $('.hide_button_element').closest( 'tr' ).hide();
                $('.hide_icon_element').closest( 'tr' ).hide();
                
            }else if('icon' == ($(this).children("option:selected").val())){
                $('.hide_icon_element').closest( 'tr' ).show();
                $('.hide_text_element').closest( 'tr' ).hide();
                $('.hide_button_element').closest( 'tr' ).hide();
            }else{
                $('.hide_text_element').closest( 'tr' ).hide();
                $('.hide_icon_element').closest( 'tr' ).hide();
                $('.hide_button_element').closest( 'tr' ).show();
            }
        });

        if('text' == ($("select.wt_button_type").children("option:selected").val()) ) {
            $('.hide_text_element').closest( 'tr' ).show();
            $('.hide_button_element').closest( 'tr' ).hide();
            $('.hide_icon_element').closest( 'tr' ).hide();
            
        }else if('icon' == ($("select.wt_button_type").children("option:selected").val())){
            $('.hide_text_element').closest( 'tr' ).hide();
            $('.hide_button_element').closest( 'tr' ).hide();
            $('.hide_icon_element').closest( 'tr' ).show();
        }else{
            $('.hide_text_element').closest( 'tr' ).hide();
            $('.hide_icon_element').closest( 'tr' ).hide();
            $('.hide_button_element').closest( 'tr' ).show();
        }

        if($('input[class="wt_enable_add_to_cart_option"]').is(':checked'))
        {
            $('.wt_enable_add_to_cart_option_element').closest( 'tr' ).show();  
        }else
        {
            $('.wt_enable_add_to_cart_option_element').closest( 'tr' ).hide();
        }

        $('input[class="wt_enable_add_to_cart_option"]').on('change', function() {
            $('.wt_enable_add_to_cart_option_element').closest( 'tr' ).show();  
            
            if ($('input[class="wt_enable_add_to_cart_option"]').is( ':checked' ) ) {
            }else{
                $('.wt_enable_add_to_cart_option_element').closest( 'tr' ).hide();
            }

        });

        if($('input[class="wt_enable_clipboard_option"]').is(':checked'))
        {
            $('.wt_enable_clipboard_option_element').closest( 'tr' ).show();  
        }else
        {
            $('.wt_enable_clipboard_option_element').closest( 'tr' ).hide();
        }

        $('input[class="wt_enable_clipboard_option"]').on('change', function() {
            $('.wt_enable_clipboard_option_element').closest( 'tr' ).show();  
            
            if ($('input[class="wt_enable_clipboard_option"]').is( ':checked' ) ) {
            }else{
                $('.wt_enable_clipboard_option_element').closest( 'tr' ).hide();
            }

        });
    });
    
    

})(jQuery);
