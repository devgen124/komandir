// (function ($) {
//     'use strict';

//     $(document).ready(function (a) {

//         if($("div").hasClass("product")){
//             if($( "span" ).hasClass( "onsale" )){
//                 if ($('span.onsale').css('position') == 'absolute'){
//                     if($( ".product div i" ).hasClass( "class-sale-price" )){
//                         $(".product div i").css("left","74px");
//                     }
//                 }
//             }
//         }

//         $('#select-all').click(function (event) {
//             if (this.checked) {
//                 $(':checkbox').each(function () {
//                     this.checked = true;
//                 });
//             } else {
//                 $(':checkbox').each(function () {
//                     this.checked = false;
//                 });
//             }
//         });


//        /* $('.webtoffee_wishlist_remove').click(function (e) {

//             e.preventDefault();
//              var $this = $(this);  
//             var product_id = $(this).data("product_id");
//             var act = 'remove';
//             var quantity = 1;
//             $.ajax({
//                 url: webtoffee_wishlist_ajax_add.add_to_wishlist,
//                 type: 'POST',
//                 data: {
//                     action: 'add_to_wishlist',
//                     product_id: product_id,
//                     act: act,
//                     quantity: quantity,
//                     wt_nonce: webtoffee_wishlist_ajax_add.wt_nonce,
//                 },
//                 success: function (response) {
//                     alert(1)
//                          $this.siblings('img').removeClass('webtoffee_wishlist_remove');
//                           $this.siblings('img').addClass('webtoffee_wishlist');
//                            $this.addClass('webtoffee_wishlist');
//                             $this.removeClass('webtoffee_wishlist_remove');
//                          if($this[0].tagName.toLowerCase() !='img'){
//                           $this.siblings('img').attr('src', response.img_change_url );
//                         }else{
//                             $this.attr('src', response.img_change_url );
                           
//                         }
                    
//                     //location.reload(); //todo remove pageload and use ajax
//                     //$(".wt-wishlist-button").addClass('webtoffee_wishlist');
//                     //$(".wt-wishlist-button").removeClass('webtoffee_wishlist_remove');

//                 }
//             });
//         });*/


//         $('.wt-wishlist-button').on('click', function (e) {

//             e.preventDefault();
//             var elm = $(this);

//             if (elm.prev().is('img')) {                
//                 elm.siblings('img').attr('src', webtoffee_wishlist_ajax_add.wishlist_loader);
//                  elm.siblings('img').css({'height': '15px' });
//             } else if(elm[0].tagName.toLowerCase() == 'img') {
//                 elm.attr('src', webtoffee_wishlist_ajax_add.wishlist_loader);
//                 elm.css({'height': '15px' });
//             }else if (elm[0].tagName.toLowerCase() == 'i') {
//                 elm.children('img').attr('src', webtoffee_wishlist_ajax_add.wishlist_loader);
//                // elm.children('img').css({'height': '15px' });
//             }
//             var product_id = $(this).data("product_id");
//             var variation_id = $("input[name=variation_id]").val();
//             var action = elm.attr('data-action');
//             var action_type = elm.attr('type-action');
//             //var act = $(this).data("act");
//             var act = action;
//             var quantity = $("input[name=quantity]").val();
//             if (!quantity) {
//                 quantity = 1;
//             }

//             $.ajax({
//                 url: webtoffee_wishlist_ajax_add.add_to_wishlist,
//                 type: 'POST',
//                 data: {
//                     action: 'add_to_wishlist',
//                     product_id: product_id,
//                     variation_id: variation_id,
//                     act: act,
//                     quantity: quantity,
//                     wt_nonce: webtoffee_wishlist_ajax_add.wt_nonce,
//                 },
//                 success: function (response) {
//                     var new_action = (action == 'remove' ? 'add' : 'remove');
//                     if (action_type == 'btn') {
//                         elm.parent('a').siblings('button').show();
//                          elm.siblings('img').attr('src', webtoffee_wishlist_ajax_add.wishlist_favourite);
//                         elm.parents('a').hide();
//                         if (response.browse_wishlist == 1) {
//                             elm.parents('a').siblings('.browse_wishlist').css("display", "none");
//                             elm.parent('a').parent('.icon_after_add_to_cart').css("line-height", "0px");
//                             elm.parent('a').parent('.icon_after_add_to_cart').css("line-height", "20px");
//                         }
//                     } else {
//                         if (elm[0].tagName.toLowerCase() == 'i') {
//                             if (response.browse_wishlist == 1) {
//                                 if (new_action == 'add') {
//                                     elm.parent('a').siblings('.browse_wishlist').css("display", "none");
//                                 } else {
//                                     elm.parent('a').siblings('.browse_wishlist').css("display", "block");
//                                 }
//                             }
//                             elm.attr('data-action', new_action);
//                             if (response.icon_position == 1) {
//                                 elm.children('img').attr('src', response.img_change_url_icon);
//                             }else{
//                             elm.children('img').attr('src', response.img_change_url);
//                            }
//                         } else if (elm[0].tagName.toLowerCase() == 'button') {
//                             elm.siblings('a').css("display", "inline-flex");
//                             elm.hide();
//                             if (response.browse_wishlist == 1) {
//                                 elm.siblings('.browse_wishlist').css("display", "block");
//                                 elm.parent('.icon_after_add_to_cart').css("line-height", "20px");
//                             }
//                         } else {
//                             if (new_action == 'add') {
//                                 if (response.browse_wishlist == 1) {
//                                     elm.parent('a').siblings('.browse_wishlist').hide();
//                                 }
//                                 if (elm[0].tagName.toLowerCase() != 'img') {
//                                     elm.text(response.wt_add_to_wishlist_text);
//                                 } else {
//                                     var selm = elm.siblings('span');
//                                     selm.text(response.wt_add_to_wishlist_text);
//                                 }
//                             } else {
//                                 if (response.browse_wishlist == 1) {
//                                     elm.parent('a').siblings('.browse_wishlist').show();
//                                 }
//                                 if (elm[0].tagName.toLowerCase() != 'img') {
//                                     elm.text(response.wt_after_adding_product_text);
//                                 } else {
//                                     var selm = elm.siblings('span');
//                                     selm.text(response.wt_after_adding_product_text);
//                                 }
//                             }
//                             elm.attr('data-action', new_action);
//                             elm.siblings('img').attr('data-action', new_action);
//                             if (elm[0].tagName.toLowerCase() != 'img') {
//                                 elm.siblings('img').attr('src', response.img_change_url);

//                             } else {
//                                 elm.attr('src', response.img_change_url);

//                             }
//                         }
//                     }

//                 },
//             });
//         });


//         $('.remove_wishlist_single').click(function (e) {

//             e.preventDefault();

//             var product_id = $(this).data("product_id");
//             var act = 'remove';
//             var quantity = 1;
//             $.ajax({
//                 url: webtoffee_wishlist_ajax_add.add_to_wishlist,
//                 type: 'POST',
//                 data: {
//                     action: 'add_to_wishlist',
//                     product_id: product_id,
//                     act: act,
//                     quantity: quantity,
//                     wt_nonce: webtoffee_wishlist_ajax_add.wt_nonce,
//                 },
//                 success: function (response) {
//                     location.reload(); //todo remove pageload and use ajax
//                     //$(".wt-wishlist-button").addClass('webtoffee_wishlist');
//                     //$(".wt-wishlist-button").removeClass('webtoffee_wishlist_remove');

//                 }
//             });
//         });


//         $('#bulk-add-to-cart').click(function (e) {
            
//             e.preventDefault();
//             //var remove_wishlist = $("input[name=remove_wishlist]").val();
//             var checked = [];
//             $(".remove_wishlist_single").each(function () {
//                 if($(this).data("product_type")){
//                     checked.push(parseInt($(this).data("variation_id")));
//                 }else{
//                     checked.push(parseInt($(this).data("product_id")));
//                 }
//             });
//             $.ajax({
//                 url: webtoffee_wishlist_ajax_myaccount_bulk_add_to_cart.myaccount_bulk_add_to_cart,
//                 type: 'POST',
//                 data: {
//                     action: 'myaccount_bulk_add_to_cart_action',
//                     product_id: checked,
//                     wt_nonce: webtoffee_wishlist_ajax_myaccount_bulk_add_to_cart.wt_nonce,

//                 },
//                 success: function (response) {
//                     if($('.single-add-to-cart').data("redirect_to_cart")){
//                         location.href = (response.redirect); 
//                     }else{
//                         var settings_div = $('<div class="eh_msg_div" style="background:#1de026; border:solid 1px #2bcc1c;">Products added to your cart</div>');				
//                         save_settings(settings_div);
//                     }
//                 }
//             });
//         });
        
//         $('.single-add-to-cart').click(function (e) {
//             e.preventDefault();
            
//             var product_id = $(this).data("product_id");
//             $.ajax({
//                 url: webtoffee_wishlist_ajax_single_add_to_cart.single_add_to_cart,
//                 type: 'POST',
//                 data: {
//                     action: 'single_add_to_cart_action',
//                     product_id: product_id,
//                     wt_nonce: webtoffee_wishlist_ajax_single_add_to_cart.wt_nonce,

//                 },
//                 success: function (response) {
//                     if($('.single-add-to-cart').data("redirect_to_cart")){
//                         location.href = (response.redirect); 
//                     }else{
//                         var settings_div = $('<div class="eh_msg_div" style="background:#1de026; border:solid 1px #2bcc1c;">Product added to your cart</div>');				
//                         save_settings(settings_div);
//                     }
//                 }
//             });
           
//         });
        
//     });

//     var save_settings = function(settings_div)
//         {
//             $('body').append(settings_div);
//             settings_div.stop(true,true).animate({'opacity':1,'top':'50px'},1000);
//             setTimeout(function(){
//                 settings_div.animate({'opacity':0,'top':'100px'},1000,function(){
//                     settings_div.remove();
//                 });
//             },3000);
//         }

// })(jQuery);