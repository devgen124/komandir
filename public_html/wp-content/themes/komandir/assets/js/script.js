/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "../node_modules/select-custom/index.js":
/*!**********************************************!*\
  !*** ../node_modules/select-custom/index.js ***!
  \**********************************************/
/***/ ((module) => {

function _classCallCheck(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function _defineProperties(t,e){for(var n=0;n<e.length;n++){var s=e[n];s.enumerable=s.enumerable||!1,s.configurable=!0,"value"in s&&(s.writable=!0),Object.defineProperty(t,s.key,s)}}function _createClass(t,e,n){return e&&_defineProperties(t.prototype,e),n&&_defineProperties(t,n),t}function _defineProperty(t,e,n){return e in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t}function ownKeys(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var s=Object.getOwnPropertySymbols(e);t&&(s=s.filter(function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable})),n.push.apply(n,s)}return n}function _objectSpread2(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?ownKeys(n,!0).forEach(function(t){_defineProperty(e,t,n[t])}):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):ownKeys(n).forEach(function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))})}return e}function offset(t){var e=t.getBoundingClientRect();return{top:e.top+document.body.scrollTop,left:e.left+document.body.scrollLeft}}function wrapElements(t,e){t.parentNode.insertBefore(e,t),e.appendChild(t)}function unwrap(t){for(var e=document.createDocumentFragment();t.firstChild;){var n=t.removeChild(t.firstChild);e.appendChild(n)}t.parentNode.replaceChild(e,t)}function detectTouch(){return"ontouchstart"in window||navigator.maxTouchPoints}Element.prototype.closest||(Element.prototype.closest=function(t){var e=this;if(!t||"string"!=typeof t)return null;for(var n=t.charAt(0);e&&e!==document;e=e.parentNode){if("."===n&&e.classList&&e.classList.contains(t.substr(1)))return e;if("#"===n&&e.id===t.substr(1))return e;if("["===n&&e.hasAttribute(t.substr(1,t.length-2)))return e;if(e.tagName.toLowerCase()===t)return e}return null});var BEMblock=function(e,n){return{name:n,block:e,addMod:function(t){e.classList.add("".concat(n,"--").concat(t))},toggleMod:function(t){e.classList.toggle("".concat(n,"--").concat(t))},removeMod:function(t){e.classList.remove("".concat(n,"--").concat(t))},containsMod:function(t){return e.classList.contains("".concat(n,"--").concat(t))}}},constants={wrap:"custom-select",opener:"custom-select__opener",panel:"custom-select__panel",option:"custom-select__option",optionsWrap:"custom-select__options",optgroup:"custom-select__optgroup",panelItemClassName:"custom-select__panel-item",openerLabel:"custom-select__opener-label",IS_OPEN:"open",IS_DISABLED:"disabled",IS_MULTIPLE:"multiple",IS_SELECTED:"selected",IS_ABOVE:"above",HAS_CUSTOM_SELECT:"has-custom-select",HAS_UNUSED_CLOSE_FUNCTION:"has-unused-close-custom-select-function",DATA_ALLOW_PANEL_CLICK:"data-allow-panel-click",DATA_LABEL:"data-label",DATA_VALUE:"data-value",DATA_HAS_PANEL_ITEM:"data-has-panel-item",DATA_LABEL_INDEX:"data-label-index"},defaultParams={optionBuilder:void 0,panelItem:{position:"",item:"",className:""},changeOpenerText:!0,multipleSelectionOnSingleClick:!0,multipleSelectOpenerText:{labels:!1,array:!1},allowPanelClick:!1,openOnHover:!1,closeOnMouseleave:!1,wrapDataAttributes:!1,openerLabel:!1};function _createElements(){var t,e,n,s,o=this,i=document.createElement("div"),c=document.createElement("div"),a=document.createElement("div"),l=this.el.options,r=this.el.querySelectorAll("optgroup");if(this.options.openerLabel&&((s=document.createElement("span")).className=this.constants.openerLabel),this.options.panelItem.item&&(e=document.createElement("div"),(n=document.createElement("div")).className=this.constants.optionsWrap,e.className=this.constants.panelItemClassName,this.el.setAttribute(this.constants.DATA_HAS_PANEL_ITEM,""),this.options.panelItem.item.classList?((t=this.options.panelItem.item.cloneNode(!0)).className=this.options.panelItem.className?this.options.panelItem.className:"",e.appendChild(t)):"string"==typeof this.options.panelItem.item&&(e.innerHTML=this.options.panelItem.item)),e&&"top"===this.options.panelItem.position&&c.appendChild(e),0<r.length){for(var p=0;p<r.length;p++){for(var d=r[p].label,u=r[p].querySelectorAll("option"),h=document.createElement("div"),m=0;m<u.length;m++){var E=document.createElement("div");if(E.classList.add(this.constants.option),E.setAttribute(this.constants.DATA_VALUE,u[m].value),E.innerHTML=u[m].innerHTML,this.addDataAttributes(u[m],E),this.addOptionItem(u[m],E),u[m].selected){BEMblock(E,this.constants.option).addMod(this.constants.IS_SELECTED),s?s.innerHTML=u[m].innerHTML:a.innerHTML=u[m].innerHTML;var L=E.querySelector('input[type="checkbox"]');L&&(L.checked=!0)}u[m].disabled&&BEMblock(E,this.constants.option).addMod(this.constants.IS_DISABLED),h.appendChild(E)}h.classList.add(this.constants.optgroup),h.setAttribute(this.constants.DATA_LABEL,d),this.addDataAttributes(r[p],h),n?n.appendChild(h):c.appendChild(h)}n&&c.appendChild(n)}else{for(var S=[],f=0;f<l.length;f++){var _=document.createElement("div");_.classList.add(this.constants.option),_.innerHTML=l[f].innerHTML,_.setAttribute(this.constants.DATA_VALUE,l[f].value),this.addDataAttributes(l[f],_),this.addOptionItem(l[f],_);var A=_.querySelector('input[type="checkbox"]');this.el.multiple?l[f].selected&&(BEMblock(_,this.constants.option).addMod(this.constants.IS_SELECTED),S.push(_),s?s.innerHTML=l[f].innerHTML:a.innerHTML=l[f].innerHTML,A&&(A.checked=!0)):l[f].selected&&(BEMblock(_,this.constants.option).addMod(this.constants.IS_SELECTED),s?s.innerHTML=l[f].innerHTML:a.innerHTML=l[f].innerHTML,A&&(A.checked=!0)),l[f].disabled&&BEMblock(_,this.constants.option).addMod(this.constants.IS_DISABLED),n?n.appendChild(_):c.appendChild(_)}if(this.options.multipleSelectOpenerText.labels&&this.options.openerLabel&&console.warn("You set `multipleSelectOpenerText: { labels: true }` and `openerLabel: true` options to this select",this.el,"It doesn't work that way. You should change one of the options."),0<S.length){var v=S.map(function(t){return t.innerText});this.options.multipleSelectOpenerText.array&&(s?s.innerHTML=v:a.innerHTML=v),this.options.multipleSelectOpenerText.labels&&S.forEach(function(t){o.setSelectOptionsItems(t,o.el,a)})}n&&c.appendChild(n)}e&&"bottom"===this.options.panelItem.position&&c.appendChild(e),this.options.allowPanelClick&&this.el.setAttribute(this.constants.DATA_ALLOW_PANEL_CLICK,""),i.classList.add(this.constants.wrap),this.options.wrapDataAttributes&&this.addDataAttributes(this.el,i);function b(t,e){t&&BEMblock(i,o.constants.wrap).addMod(e)}b(this.el.disabled,this.constants.IS_DISABLED),b(this.el.multiple,this.constants.IS_MULTIPLE),c.classList.add(this.constants.panel),a.classList.add(this.constants.opener),s&&a.appendChild(s),wrapElements(this.el,i),i.appendChild(a),i.appendChild(c)}function _open(){var t=this.options.openOnHover&&!detectTouch()?"mouseenter":"click";this.openSelectBind=this.openSelect.bind(this),this.opener.addEventListener(t,this.openSelectBind)}function _close(){this.options.closeOnMouseleave&&!detectTouch()&&this.select.addEventListener("mouseleave",function(){document.body.click()}),document.documentElement.classList.contains(this.constants.HAS_CUSTOM_SELECT)||(this.closeSelectBind=this.closeSelect.bind(this),document.addEventListener("click",this.closeSelectBind),document.addEventListener("keydown",this.closeSelectBind),document.documentElement.classList.add(this.constants.HAS_CUSTOM_SELECT),this.closeSelectListenersAdded=!0)}function _change(){for(var o=this,i=this.el.options,c=this.select.querySelectorAll("."+this.constants.option),t=function(s){c[s].addEventListener("click",function(t){if(!o.el.disabled){var e=t.currentTarget;if(!BEMblock(e,o.constants.option).containsMod(o.constants.IS_DISABLED)){var n=o.options.openerLabel?o.opener.children[0]:o.opener;o.setSelectedOptions({e:t,clickedCustomOption:e,nativeOptionsList:i,customOptionsList:c,item:s}),o.dispatchEvent(o.el),o.triggerCheckbox(e),o.options.changeOpenerText&&(o.el.multiple&&o.options.multipleSelectOpenerText.array?o.getSelectOptionsText(o.el)&&(n.innerHTML=o.getSelectOptionsText(o.el)):o.el.multiple&&o.options.multipleSelectOpenerText.labels?o.setSelectOptionsItems(e,o.el,o.opener):o.el.multiple&&!o.options.multipleSelectOpenerText?n.innerHTML=n.innerHTML:n.innerHTML=e.innerText)}}})},e=0;e<c.length;e++)t(e)}function _trigerCustomEvents(){var e=this;new MutationObserver(function(t){t.forEach(function(t){BEMblock(t.target,e.constants.wrap).containsMod(e.constants.IS_OPEN)?-1===t.oldValue.indexOf("".concat(e.constants.wrap,"--").concat(e.constants.IS_OPEN))&&e.onOpen&&e.onOpen(t.target):0<t.oldValue.indexOf("".concat(e.constants.wrap,"--").concat(e.constants.IS_OPEN))&&(BEMblock(e.panel,e.constants.panel).removeMod(e.constants.IS_ABOVE),e.onClose&&e.onClose(t.target))})}).observe(this.select,{attributes:!0,attributeOldValue:!0,attributeFilter:["class"]})}function _destroy(){(document.querySelectorAll(".".concat(this.constants.wrap)).length||document.documentElement.classList.contains(this.constants.HAS_UNUSED_CLOSE_FUNCTION))&&(this.select.classList.contains(this.constants.wrap)&&(this.opener.parentNode.removeChild(this.opener),this.panel.parentNode.removeChild(this.panel),unwrap(this.select),this.el.removeAttribute(this.constants.DATA_HAS_PANEL_ITEM),this.el.removeAttribute(this.constants.DATA_ALLOW_PANEL_CLICK)),document.querySelectorAll(".".concat(this.constants.wrap)).length||(document.documentElement.classList.remove(this.constants.HAS_CUSTOM_SELECT),this.closeSelectListenersAdded?(document.removeEventListener("click",this.closeSelectBind),document.removeEventListener("keydown",this.closeSelectBind),document.documentElement.classList.remove(this.constants.HAS_UNUSED_CLOSE_FUNCTION),this.closeSelectListenersAdded=!1):document.documentElement.classList.add(this.constants.HAS_UNUSED_CLOSE_FUNCTION)))}var Select=function(){function n(t,e){_classCallCheck(this,n),this.el=t,this.options=_objectSpread2({},defaultParams,{},e),this.constants=constants}return _createClass(n,[{key:"init",value:function(){_createElements.call(this),_open.call(this),_close.call(this),_change.call(this),_trigerCustomEvents.call(this)}},{key:"destroy",value:function(){_destroy.call(this)}},{key:"dispatchEvent",value:function(t){var e=document.createEvent("HTMLEvents");e.initEvent("change",!0,!1),t.dispatchEvent(e)}},{key:"triggerCheckbox",value:function(t){var e=t.querySelector('input[type="checkbox"]'),n=BEMblock(t,this.constants.option).containsMod(this.constants.IS_SELECTED);e&&n&&(e.checked=!0),e&&!n&&(e.checked=!1)}},{key:"addOptionItem",value:function(t,e){this.options.optionBuilder&&this.options.optionBuilder(t,e)}},{key:"openSelect",value:function(t){if(!this.el.disabled&&!t.target.closest("[".concat(this.constants.DATA_LABEL_INDEX,"]"))){var e=document.querySelectorAll(".".concat(this.constants.wrap,"--").concat(this.constants.IS_OPEN));BEMblock(this.select,this.constants.wrap).toggleMod(this.constants.IS_OPEN);for(var n=0;n<e.length;n++)BEMblock(e[n],this.constants.wrap).removeMod(this.constants.IS_OPEN);this.setPanelPosition()}}},{key:"closeSelect",value:function(t){if(document.documentElement.classList.contains(this.constants.HAS_UNUSED_CLOSE_FUNCTION)&&console.warn("You have unused `closeSelect` function, triggering on document click and `Esc` button. You shoud remove it, by using `destroy()` method to the first select element."),(!t.type||"keydown"!==t.type||!t.keyCode||27===t.keyCode)&&document.documentElement.classList.contains(this.constants.HAS_CUSTOM_SELECT)&&!t.target.closest("[".concat(this.constants.DATA_LABEL_INDEX,"]"))){var e=document.querySelectorAll(".".concat(this.constants.wrap,"--").concat(this.constants.IS_OPEN));if(e.length&&!(t.type&&"click"===t.type&&t.target.closest(".".concat(this.constants.wrap,"--").concat(this.constants.IS_DISABLED))||t.type&&"click"===t.type&&t.target.closest(".".concat(this.constants.option,"--").concat(this.constants.IS_DISABLED))||t.type&&"click"===t.type&&t.target.hasAttribute(this.constants.DATA_LABEL))){if(t.type&&"click"===t.type&&t.target.closest("."+this.constants.wrap)){var n=t.target.closest("."+this.constants.wrap).querySelector("select");if(n.multiple&&t.type&&"click"===t.type&&t.target.closest("."+this.constants.panel))return;if(n.hasAttribute(this.constants.DATA_ALLOW_PANEL_CLICK)&&t.type&&"click"===t.type&&t.target.closest("."+this.constants.panel))return;if(n.hasAttribute(this.constants.DATA_HAS_PANEL_ITEM)&&t.type&&"click"===t.type&&t.target.closest("."+this.constants.panelItemClassName))return}if(-1===t.target.className.indexOf(this.constants.opener))for(var s=0;s<e.length;s++)BEMblock(e[s],this.constants.wrap).removeMod(this.constants.IS_OPEN)}}}},{key:"setSelectedOptionsMultiple",value:function(t){var e=t.clickedCustomOption,n=t.nativeOptionsList,s=t.item;n[s].selected?(n[s].selected=!1,BEMblock(e,this.constants.option).removeMod(this.constants.IS_SELECTED)):(n[s].selected=!0,BEMblock(e,this.constants.option).addMod(this.constants.IS_SELECTED))}},{key:"setSelectedOptionsDefault",value:function(t){for(var e=t.clickedCustomOption,n=t.nativeOptionsList,s=t.customOptionsList,o=t.item,i=0;i<n.length;i++){var c=s[i].querySelector('input[type="checkbox"]');n[i].selected=!1,BEMblock(s[i],this.constants.option).removeMod(this.constants.IS_SELECTED),c&&(c.checked=!1)}var a=e.querySelector('input[type="checkbox"]');BEMblock(e,this.constants.option).addMod(this.constants.IS_SELECTED),n[o].selected=!0,a&&(a.checked=!0)}},{key:"setSelectedOptions",value:function(t){var e=t.e,n=t.clickedCustomOption,s=t.nativeOptionsList,o=t.customOptionsList,i=t.item;this.el.multiple&&(this.options.multipleSelectionOnSingleClick||e.ctrlKey)?this.setSelectedOptionsMultiple({clickedCustomOption:n,nativeOptionsList:s,item:i}):this.setSelectedOptionsDefault({clickedCustomOption:n,nativeOptionsList:s,customOptionsList:o,item:i})}},{key:"setPanelPosition",value:function(){offset(this.panel).top+this.panel.offsetHeight>=window.innerHeight?BEMblock(this.panel,this.constants.panel).addMod(this.constants.IS_ABOVE):BEMblock(this.panel,this.constants.panel).removeMod(this.constants.IS_ABOVE)}},{key:"getSelectOptionsText",value:function(t){var e=[].slice.call(t.options),n=[];return e.forEach(function(t){t.selected&&n.push(t.innerText)}),n.join(", ")}},{key:"setSelectOptionsItems",value:function(t,e,n){var s=this,o=[].slice.call(t.parentNode.children),i=[].slice.call(e.options),c=o.map(function(t,e){var n=document.createElement("span");return n.className=s.constants.openerLabel,n.setAttribute(s.constants.DATA_LABEL_INDEX,e),n.innerHTML="".concat(t.innerText,"<button></button>"),n});o.forEach(function(t,e){t.setAttribute(s.constants.DATA_LABEL_INDEX,e)}),i.forEach(function(t,e){t.setAttribute(s.constants.DATA_LABEL_INDEX,e)});var a=+t.getAttribute(this.constants.DATA_LABEL_INDEX),l=n.querySelector("[".concat(this.constants.DATA_LABEL_INDEX,'="').concat(a,'"]'));BEMblock(t,this.constants.option).containsMod(this.constants.IS_SELECTED)?(n.children.length||(n.innerHTML=""),n.appendChild(c[a])):l&&n.removeChild(l),c.forEach(function(t){t.querySelector("button").addEventListener("click",function(t){var e=this;t.preventDefault();var n=t.currentTarget.parentNode,s=n.getAttribute(this.constants.DATA_LABEL_INDEX),o=[].slice.call(this.select.querySelectorAll("[".concat(this.constants.DATA_LABEL_INDEX,'="').concat(s,'"]'))),i=o.filter(function(t){if(t.classList.contains(e.constants.option))return t})[0];o.forEach(function(t){t.selected&&(t.selected=!1),BEMblock(t,e.constants.option).containsMod(e.constants.IS_SELECTED)&&BEMblock(t,e.constants.option).removeMod(e.constants.IS_SELECTED)}),this.dispatchEvent(this.el),this.triggerCheckbox(i),n.parentNode&&n.parentNode.removeChild(n)}.bind(s))})}},{key:"addDataAttributes",value:function(t,e){var n=[].filter.call(t.attributes,function(t){return/^data-/.test(t.name)});n.length&&n.forEach(function(t){e.setAttribute(t.name,t.value)})}},{key:"select",get:function(){return this.el.parentNode}},{key:"opener",get:function(){return this.select.querySelector("."+this.constants.opener)}},{key:"panel",get:function(){return this.select.querySelector("."+this.constants.panel)}}]),n}();module.exports=Select;


/***/ }),

/***/ "./js/ajax_wishlist.js":
/*!*****************************!*\
  !*** ./js/ajax_wishlist.js ***!
  \*****************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ ajax_wishlist)
/* harmony export */ });


function ajax_wishlist() {
    $(function () {

        initWishList();

        $(document).on('berocket_ajax_products_loaded', initWishList);

        function initWishList() {
            
            if ($("div").hasClass("product")) {
                if ($("span").hasClass("onsale")) {
                    if ($('span.onsale').css('position') == 'absolute') {
                        if ($(".product div i").hasClass("class-sale-price")) {
                            $(".product div i").css("left", "74px");
                        }
                    }
                }
            }
    
            $('#select-all').on('click', function (event) {
                if (this.checked) {
                    $(':checkbox').each(function () {
                        this.checked = true;
                    });
                } else {
                    $(':checkbox').each(function () {
                        this.checked = false;
                    });
                }
            });
    
    
            /* $('.webtoffee_wishlist_remove').on('click', function (e) {
     
                 e.preventDefault();
                  var $this = $(this);  
                 var product_id = $(this).data("product_id");
                 var act = 'remove';
                 var quantity = 1;
                 $.ajax({
                     url: webtoffee_wishlist_ajax_add.add_to_wishlist,
                     type: 'POST',
                     data: {
                         action: 'add_to_wishlist',
                         product_id: product_id,
                         act: act,
                         quantity: quantity,
                         wt_nonce: webtoffee_wishlist_ajax_add.wt_nonce,
                     },
                     success: function (response) {
                         alert(1)
                              $this.siblings('img').removeClass('webtoffee_wishlist_remove');
                               $this.siblings('img').addClass('webtoffee_wishlist');
                                $this.addClass('webtoffee_wishlist');
                                 $this.removeClass('webtoffee_wishlist_remove');
                              if($this[0].tagName.toLowerCase() !='img'){
                               $this.siblings('img').attr('src', response.img_change_url );
                             }else{
                                 $this.attr('src', response.img_change_url );
                                
                             }
                         
                         //location.reload(); //todo remove pageload and use ajax
                         //$(".wt-wishlist-button").addClass('webtoffee_wishlist');
                         //$(".wt-wishlist-button").removeClass('webtoffee_wishlist_remove');
     
                     }
                 });
             });*/
    
            $('.wt-wishlist-button').on('click', function (e) {
    
                e.preventDefault();
                var elm = $(this);
    
                if (elm.prev().is('img')) {
                    elm.siblings('img').attr('src', webtoffee_wishlist_ajax_add.wishlist_loader);
                    elm.siblings('img').css({ 'height': '15px' });
                } else if (elm[0].tagName.toLowerCase() == 'img') {
                    elm.attr('src', webtoffee_wishlist_ajax_add.wishlist_loader);
                    elm.css({ 'height': '15px' });
                } else if (elm[0].tagName.toLowerCase() == 'i') {
                    elm.children('img').attr('src', webtoffee_wishlist_ajax_add.wishlist_loader);
                    // elm.children('img').css({'height': '15px' });
                }
                var product_id = $(this).data("product_id");
                var variation_id = $("input[name=variation_id]").val();
                var action = elm.attr('data-action');
                var action_type = elm.attr('type-action');
                //var act = $(this).data("act");
                var act = action;
                var quantity = $("input[name=quantity]").val();
                if (!quantity) {
                    quantity = 1;
                }

                console.log(webtoffee_wishlist_ajax_add.add_to_wishlist);
    
                $.ajax({
                    url: webtoffee_wishlist_ajax_add.add_to_wishlist,
                    type: 'POST',
                    data: {
                        action: 'add_to_wishlist',
                        product_id: product_id,
                        variation_id: variation_id,
                        act: act,
                        quantity: quantity,
                        wt_nonce: webtoffee_wishlist_ajax_add.wt_nonce,
                    },
                    success: function (response) {
                        var new_action = (action == 'remove' ? 'add' : 'remove');
                        if (action_type == 'btn') {
                            elm.parent('a').siblings('button').show();
                            elm.siblings('img').attr('src', webtoffee_wishlist_ajax_add.wishlist_favourite);
                            elm.parents('a').hide();
                            if (response.browse_wishlist == 1) {
                                elm.parents('a').siblings('.browse_wishlist').css("display", "none");
                                elm.parent('a').parent('.icon_after_add_to_cart').css("line-height", "0px");
                                elm.parent('a').parent('.icon_after_add_to_cart').css("line-height", "20px");
                            }
                        } else {
                            if (elm[0].tagName.toLowerCase() == 'i') {
                                console.log(response);
                                if (response.browse_wishlist == 1) {
                                    if (new_action == 'add') {
                                        elm.parent('a').siblings('.browse_wishlist').css("display", "none");
                                    } else {
                                        elm.parent('a').siblings('.browse_wishlist').css("display", "block");
                                    }
                                }
                                elm.attr('data-action', new_action);
                                if (response.icon_position == 1) {
                                    elm.children('img').attr('src', response.img_change_url_icon);
                                } else {
                                    elm.children('img').attr('src', response.img_change_url);
                                }
                            } else if (elm[0].tagName.toLowerCase() == 'button') {
                                elm.siblings('a').css("display", "inline-flex");
                                elm.hide();
                                if (response.browse_wishlist == 1) {
                                    elm.siblings('.browse_wishlist').css("display", "block");
                                    elm.parent('.icon_after_add_to_cart').css("line-height", "20px");
                                }
                            } else {
                                if (new_action == 'add') {
                                    if (response.browse_wishlist == 1) {
                                        elm.parent('a').siblings('.browse_wishlist').hide();
                                    }
                                    if (elm[0].tagName.toLowerCase() != 'img') {
                                        elm.text(response.wt_add_to_wishlist_text);
                                    } else {
                                        var selm = elm.siblings('span');
                                        selm.text(response.wt_add_to_wishlist_text);
                                    }
                                } else {
                                    if (response.browse_wishlist == 1) {
                                        elm.parent('a').siblings('.browse_wishlist').show();
                                    }
                                    if (elm[0].tagName.toLowerCase() != 'img') {
                                        elm.text(response.wt_after_adding_product_text);
                                    } else {
                                        var selm = elm.siblings('span');
                                        selm.text(response.wt_after_adding_product_text);
                                    }
                                }
                                elm.attr('data-action', new_action);
                                elm.siblings('img').attr('data-action', new_action);
                                if (elm[0].tagName.toLowerCase() != 'img') {
                                    elm.siblings('img').attr('src', response.img_change_url);
    
                                } else {
                                    elm.attr('src', response.img_change_url);
    
                                }
                            }
                        }
    
                    },
                });
    
                $.ajax({
                    url: dataObj.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'update_wishlist_count'
                    },
                    success: function (response) {
                        const $wishcount = $('.wishlist-count');
                        console.log(response);
                        if (response == 0) {
                            $wishcount.addClass('wishlist-count-empty');
                        } else {
                            $wishcount.removeClass('wishlist-count-empty');
                        }
                        $wishcount.text(response);
                    }
                });
            });
    
    
            $('.remove_wishlist_single').on('click', function (e) {
    
                e.preventDefault();
    
                var product_id = $(this).data("product_id");
                var act = 'remove';
                var quantity = 1;
                $.ajax({
                    url: webtoffee_wishlist_ajax_add.add_to_wishlist,
                    type: 'POST',
                    data: {
                        action: 'add_to_wishlist',
                        product_id: product_id,
                        act: act,
                        quantity: quantity,
                        wt_nonce: webtoffee_wishlist_ajax_add.wt_nonce,
                    },
                    success: function (response) {
                        location.reload(); //todo remove pageload and use ajax
                        //$(".wt-wishlist-button").addClass('webtoffee_wishlist');
                        //$(".wt-wishlist-button").removeClass('webtoffee_wishlist_remove');
    
                    }
                });
            });
    
    
            $('#bulk-add-to-cart').on('click', function (e) {
    
                e.preventDefault();
                //var remove_wishlist = $("input[name=remove_wishlist]").val();
                var checked = [];
                $(".remove_wishlist_single").each(function () {
                    if ($(this).data("product_type")) {
                        checked.push(parseInt($(this).data("variation_id")));
                    } else {
                        checked.push(parseInt($(this).data("product_id")));
                    }
                });
                $.ajax({
                    url: webtoffee_wishlist_ajax_myaccount_bulk_add_to_cart.myaccount_bulk_add_to_cart,
                    type: 'POST',
                    data: {
                        action: 'myaccount_bulk_add_to_cart_action',
                        product_id: checked,
                        wt_nonce: webtoffee_wishlist_ajax_myaccount_bulk_add_to_cart.wt_nonce,
    
                    },
                    success: function (response) {
                        if ($('.single-add-to-cart').data("redirect_to_cart")) {
                            location.href = (response.redirect);
                        } else {
                            var settings_div = $('<div class="eh_msg_div" style="background:#1de026; border:solid 1px #2bcc1c;">Products added to your cart</div>');
                            save_settings(settings_div);
                        }
                    }
                });
            });

            $('#bulk-clear-wishlist').on('click', function (e) {
                e.preventDefault();
                $.ajax({
                    url: webtoffee_wishlist_ajax_myaccount_clear_list.myaccount_clear_list,
                    type: 'POST',
                    data: {
                        action: 'myaccount_clear_list',
                        wt_nonce: webtoffee_wishlist_ajax_myaccount_clear_list.clear_list
                    },
                    success: function () {
                        location.reload();
                    }
                });
            });
    
            $('.single-add-to-cart').on('click', function (e) {
                e.preventDefault();
    
                var product_id = $(this).data("product_id");
                $.ajax({
                    url: webtoffee_wishlist_ajax_single_add_to_cart.single_add_to_cart,
                    type: 'POST',
                    data: {
                        action: 'single_add_to_cart_action',
                        product_id: product_id,
                        wt_nonce: webtoffee_wishlist_ajax_single_add_to_cart.wt_nonce,
    
                    },
                    success: function (response) {
                        if ($('.single-add-to-cart').data("redirect_to_cart")) {
                            location.href = (response.redirect);
                        } else {
                            var settings_div = $('<div class="eh_msg_div" style="background:#1de026; border:solid 1px #2bcc1c;">Product added to your cart</div>');
                            save_settings(settings_div);
                        }
                    }
                });
    
            });
    
            var save_settings = function (settings_div) {
                $('body').append(settings_div);
                settings_div.stop(true, true).animate({ 'opacity': 1, 'top': '50px' }, 1000);
                setTimeout(function () {
                    settings_div.animate({ 'opacity': 0, 'top': '100px' }, 1000, function () {
                        settings_div.remove();
                    });
                }, 3000);
            }

        }
    });
}

/***/ }),

/***/ "./js/attributes-list-mobile.js":
/*!**************************************!*\
  !*** ./js/attributes-list-mobile.js ***!
  \**************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ initAttrMobileToggler)
/* harmony export */ });
const $attrsBlocks = $('.woo-attributes');
const isMobile = document.documentElement.clientWidth < 576;

function initAttrMobileToggler() {
    $(function () {
        if ($attrsBlocks.length && isMobile) {
            $attrsBlocks.each((i, attrs) => {
                const $heading = $(attrs).find('.woo-attributes-heading');
                const $cont = $(attrs).find('.woo-attributes-inner');
                const $hide = $(attrs).find('.woo-attributes-hide');
        
                $heading.on('click', () => {
                    $heading.addClass('woo-attributes-heading-active');
                    $cont.slideDown();
                });
        
                $hide.on('click', () => {
                    $heading.removeClass('woo-attributes-heading-active');
                    $cont.slideUp();
                });
            });
        }
    });
}

/***/ }),

/***/ "./js/catalog_mobile_slide.js":
/*!************************************!*\
  !*** ./js/catalog_mobile_slide.js ***!
  \************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ CatalogMobileSlider)
/* harmony export */ });
class CatalogMobileSlider {
    catalogWrapper = document.querySelector('.catalog-wrapper');
    viewportwidth = document.documentElement.clientWidth;
    coordinateX = 0;

    constructor () {
        document.addEventListener("DOMContentLoaded", () => {

            const isMobile = this.viewportwidth < 992;

            if (isMobile && this.catalogWrapper) {
                const parentLinks = this.catalogWrapper.querySelectorAll('.categories-parent-list-item > a');
        
                parentLinks.forEach((link) => {
                    link.addEventListener('click', (e) => {
                        const thisLink = e.target;
                        const catId = thisLink.dataset.productCat;

                        if (catId) {
                            e.preventDefault();
                            this.ajaxLoadCategories(catId, (res) => {
                                this.renderCategories('.catalog-children-slide', res);
                                this.moveSlider('forward');
                            });
                        }
                    })
                });
            }
        });
    }

    ajaxLoadCategories (id, callback) {
        const data = new FormData();
        data.append('action', 'get_children_cats');
        data.append('cat_id', id);

        fetch(dataObj.ajaxurl, {
            method: 'POST',
            body: data
        })
        .then(response => response.text())
        .then(callback)
        .catch(err => console.log(err));
    }

    renderCategories (slideSelector, html) {
        const slide = this.catalogWrapper.querySelector(slideSelector);
        slide.innerHTML = html;

        const childLinks = slide.querySelectorAll('.catalog-slide-item > a');
        const backLink = slide.querySelector('.catalog-slide-backlink');

        childLinks.forEach((link) => {
            link.addEventListener('click', (e) => {
                const thisLink = e.target;
                const catId = thisLink.dataset.productCat;

                if (catId) {
                    e.preventDefault();
                    this.ajaxLoadCategories(catId, (res) => {
                        this.renderCategories('.catalog-grandchildren-slide', res);
                        this.moveSlider('forward');
                    });
                }
            })
        });

        backLink.addEventListener('click', (e) => {
            e.preventDefault();
            this.moveSlider('backward');
        });
    }

    moveSlider (direction) {
        if (direction === 'backward') {
            this.coordinateX += this.viewportwidth;
        } else {
            this.coordinateX -= this.viewportwidth;
        }

        this.catalogWrapper.style.transform = `translateX(${this.coordinateX}px)`;
    }
}

/***/ }),

/***/ "./js/custom_select.js":
/*!*****************************!*\
  !*** ./js/custom_select.js ***!
  \*****************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ customizeSelects)
/* harmony export */ });
/* harmony import */ var select_custom__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! select-custom */ "../node_modules/select-custom/index.js");

function customizeSelects() {

    initSelects();

    $(document).on('berocket_ajax_products_loaded', initSelects);

    function initSelects() {
        const selects = document.querySelectorAll('select:not(.country_select)');

        if (selects.length) {
            selects.forEach((el) => {
                const select = new select_custom__WEBPACK_IMPORTED_MODULE_0__(el, {
                    optionBuilder: undefined,
                    panelItem: { position: '', item: '', className: '' },
                    changeOpenerText: true,
                    multipleSelectionOnSingleClick: false,
                    multipleSelectOpenerText: { labels: false, array: false },
                    allowPanelClick: false,
                    openOnHover: false,
                    closeOnMouseleave: false,
                    wrapDataAttributes: false,
                    openerLabel: false,
                });
                select.init();
            });
        }
    }
}


/***/ }),

/***/ "./js/filters_mobile.js":
/*!******************************!*\
  !*** ./js/filters_mobile.js ***!
  \******************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ initSidebarToggler)
/* harmony export */ });
const filtersBtn = document.querySelector('.filters-btn');
const filtersSidebar = document.querySelector('.filters-sidebar');
const isMobile = document.documentElement.clientWidth < 576;

function initSidebarToggler() {

    togglesidebar();

    $(document).on('berocket_ajax_products_loaded', togglesidebar);

    function togglesidebar() {
        if (filtersBtn && filtersSidebar && isMobile) {
            const toggleSidebarVisibility = () => {
                filtersSidebar.classList.toggle('filters-sidebar-visible');
                document.body.classList.toggle('scroll-lock');
            }
        
            filtersBtn.addEventListener('click', toggleSidebarVisibility);
            const closeBtn = filtersSidebar.querySelector('.filters-close');
            closeBtn.addEventListener('click', toggleSidebarVisibility);
        }
    }
}

/***/ }),

/***/ "./js/grid-switcher.js":
/*!*****************************!*\
  !*** ./js/grid-switcher.js ***!
  \*****************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ initGridSwitcher)
/* harmony export */ });
const gridSwitcher = document.querySelector('.grid-switcher');
const productsBlock = document.querySelector('.products');

function initGridSwitcher() {
    if (gridSwitcher && productsBlock) {
        const switchButtons = gridSwitcher.querySelectorAll('.grid-view');

        const removeActiveButtons = () => {
            switchButtons.forEach(btn => {
                btn.classList.remove('grid-view--active');
            });
        }

        const restoreActiveButtons = () => {
            const view = localStorage.getItem('grid-view');
            if (view) {
                switchButtons.forEach(btn => {
                    btn.classList.remove('grid-view--active');
    
                    if (view === btn.dataset.grid) {
                        btn.classList.add('grid-view--active');
                        productsBlock.classList.remove('grid', 'list');
                        productsBlock.classList.add(view);
                    }
                });
            }
        }

        restoreActiveButtons();

        switchButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                removeActiveButtons();
                const thisBtn = e.target.closest('.grid-view');
                const view = thisBtn.dataset.grid;
                thisBtn.classList.add('grid-view--active');
                productsBlock.classList.remove('grid', 'list');
                productsBlock.classList.add(view);
                localStorage.setItem('grid-view', view);
            });
        });
    }
}


/***/ }),

/***/ "./js/mobile_menu.js":
/*!***************************!*\
  !*** ./js/mobile_menu.js ***!
  \***************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ mobileSideMenu)
/* harmony export */ });


function mobileSideMenu() {
  window.addEventListener('DOMContentLoaded', () => {
    const mobileOpen = document.querySelector('.middle-menu-mobile-btn');
    const menu = document.querySelector('.mobile-side-menu');

    if (mobileOpen && menu) {
        mobileOpen.addEventListener('click', () => {
            menu.classList.add('mobile-side-menu--opened');
        });

        const mobileClose = menu.querySelector('.mobile-side-menu-close');
        mobileClose.addEventListener('click', () => {
             menu.classList.remove('mobile-side-menu--opened');
        });
    }
  });
}

/***/ }),

/***/ "./js/mobile_search.js":
/*!*****************************!*\
  !*** ./js/mobile_search.js ***!
  \*****************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ mobileSearch)
/* harmony export */ });


function mobileSearch() {
  window.addEventListener('DOMContentLoaded', () => {
    const searchOpen = document.querySelector('.livesearch-mobile-btn');
    const searchPanel = document.querySelector('.livesearch-wrapper');

    if (searchOpen && searchPanel) {
      searchOpen.addEventListener('click', () => {
        searchPanel.classList.add('livesearch-wrapper--opened');
      });

      const searchClose = searchPanel.querySelector('.livesearch-mobile-back');
      searchClose.addEventListener('click', () => {
        searchPanel.classList.remove('livesearch-wrapper--opened');
      });
    }
  }); 
}

/***/ }),

/***/ "./js/modal.js":
/*!*********************!*\
  !*** ./js/modal.js ***!
  \*********************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ modalInit)
/* harmony export */ });
function modalInit() {
  const $modalLink = $('a[href*="#modal-"]');

  $modalLink && $modalLink.magnificPopup({
    type: 'inline',
    midClick: true,
    callbacks: {
      open: () => {
        $('.wpcf7-tel').mask('+79999999999');
      }
    }
  });

  document.addEventListener( 'wpcf7submit', function( e ) {
    $(e.target).find('p').hide();
    $(e.target.closest('.custom-popup')).find('h2 + p').hide();
  }, false );
}


/***/ }),

/***/ "./js/pass-view-switcher.js":
/*!**********************************!*\
  !*** ./js/pass-view-switcher.js ***!
  \**********************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ initPassViewSwitcher)
/* harmony export */ });
function initPassViewSwitcher(element) {
    const showPassButtons = element.querySelectorAll('.show-pass');

    if (showPassButtons.length) {
        showPassButtons.forEach((btn) => {
            btn.onclick = (e) => {
                e.preventDefault();
                const inp = e.target.previousElementSibling;

                if (inp) {
                    if (inp.type === 'password') {
                        inp.type = 'text';
                    } else {
                        inp.type = 'password';
                    }
                }
            };
        });
    }
}

/***/ }),

/***/ "./js/phone-mask.js":
/*!**************************!*\
  !*** ./js/phone-mask.js ***!
  \**************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ phoneMask)
/* harmony export */ });
function phoneMask () {
  const $phoneMask = $('.phone-mask');

  if ($phoneMask) {
    $phoneMask.mask('+79999999999');
  }
} 

/***/ }),

/***/ "./js/popup.js":
/*!*********************!*\
  !*** ./js/popup.js ***!
  \*********************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ initPopups)
/* harmony export */ });
/* harmony import */ var _pass_view_switcher_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./pass-view-switcher.js */ "./js/pass-view-switcher.js");



class Popup {

    initLogin = () => {

        this.popup = document.querySelector('#login-popup');

        this.renderSendCode();
    }

    initChangePhone = () => {

        this.popup = document.querySelector('#change-phone-number-popup');

        this.renderChangePhoneSendCode();
    }

    renderSendCode = () => {
        const sendCodeTemplate = document.querySelector('#login-send-code').content.cloneNode(true);
        this.render(sendCodeTemplate);

        const thisSection = this.popup.querySelector('.custom-popup-section');
        const sendCodeSubmit = thisSection.querySelector('.custom-popup-submit');
        const telInput = thisSection.querySelector('input[type="tel"]');
        const loginPassLink = thisSection.querySelector('.login-pass-link');

        this.setMask(telInput);

        this.addFormListeners(sendCodeSubmit, 'login_send_code', ['phone'], this.renderSmsCode);

        this.prevSection = 'send_code';

        this.addLoginPassListener(loginPassLink);
    }

    renderChangePhoneSendCode = () => {
        const sendCodeTemplate = document.querySelector('#change-phone-number-send-code').content.cloneNode(true);
        this.render(sendCodeTemplate);

        const thisSection = this.popup.querySelector('.custom-popup-section');
        const sendCodeSubmit = thisSection.querySelector('.custom-popup-submit');
        const telInput = thisSection.querySelector('input[type="tel"]');

        this.setMask(telInput);

        this.addFormListeners(sendCodeSubmit, 'change_phone_send_code', ['phone'], this.renderChangePhoneSmsCode);
    }

    setMask = (inp) => {
        $(inp).mask('+79999999999');
    }

    addFormListeners = (submitBtn, action, inputNamesArr, successCb = null) => {
        submitBtn.onclick = (e) => {
            e.preventDefault();
            const thisBtn = e.target;
            this.addLoading(thisBtn);

            const thisForm = e.target.closest('form');
            const thisSection = e.target.closest('.custom-popup-section');

            this.ajaxSend(action, this.getFormValues(thisForm, inputNamesArr), (res) => {

                this.removeLoading(thisBtn);

                this.removeWarning(thisSection);
                this.removeInvalidInputs(thisSection);

                if (res['console_message']) {
                    console.log(res['console_message']);
                }

                if (res['error_message']) {
                    this.addWarnings(thisSection, res['error_message']);
                }

                if (res['error_fields']) {
                    this.setInvalidInputs(thisSection, res['error_fields']);
                }

                if (res['phone_number']) {
                    this.phoneNumber = res['phone_number'];
                }

                if (res['reload']) {
                    location.reload();
                }

                if (res['success'] && typeof successCb === 'function') {
                    successCb();
                }
            });
        }
    }

    addLoginPassListener = (link) => {
        link.onclick = (e) => {
            e.preventDefault();
            this.renderLoginPass();
        }
    }

    addLoading = (btn) => {
        btn.classList.add('loading');
        const bodyOverlay = document.createElement('div');
        bodyOverlay.classList.add('popup-body-overlay');
        document.body.prepend(bodyOverlay);
        const innerOverlay = document.createElement('div');
        innerOverlay.classList.add('popup-inner-overlay');
        this.popup.prepend(innerOverlay);
    };

    removeLoading = (btn) => {
        btn.classList.remove('loading');
        const bodyOverlay = document.querySelector('.popup-body-overlay');
        bodyOverlay.remove();
        const innerOverlay = this.popup.querySelector('.popup-inner-overlay');
        innerOverlay.remove();
    }

    getFormValues = (formSelector, namesArr) => {
        const valuesObj = {};
        namesArr.forEach(name => {
            const input = formSelector.querySelector(`input[name="${name}"]`);
            valuesObj[name] = input.value;
        });

        return valuesObj;
    }

    addWarnings = (section, warnings) => {
        const heading = section.querySelector('h2');

        const warningsWrapper = document.createDocumentFragment();

        warnings.forEach((it) => {
            const warning = document.createElement('div');
            warning.classList.add('custom-popup-warning');
            warning.textContent = it;
            warningsWrapper.append(warning);
        });

        heading.after(...warningsWrapper.children);
    }

    removeWarning = (section) => {
        const warnings = section.querySelectorAll('.custom-popup-warning');

        if (warnings.length) {
            warnings.forEach(it => it.remove());
        }
    }

    setInvalidInputs = (section, inpArr) => {
        inpArr.forEach(inp => {
            const invalidInput = section.querySelector(`input[name="${inp}"]`);
            invalidInput.classList.add('invalid');
            console.log(invalidInput);
            invalidInput.oninput = () => {
                console.log('oninput');
                invalidInput.classList.remove('invalid');
            };
        });
    }

    removeInvalidInputs = (section) => {
        const inputs = section.querySelectorAll('input.invalid');
        if (inputs.length) {
            inputs.forEach(inp => {
                inp.classList.remove('invalid');
            });
        }
    }

    ajaxSend = (action, dataObject, callback) => {
        const data = new FormData();
        data.append('action', action);
        data.append('nonce_code', dataObj.nonce);

        if (dataObject) {
            Object.entries(dataObject).forEach((([key, value]) => {
                data.append(key, value);
            }));
        }

        fetch(dataObj.ajaxurl, {
            method: 'POST',
            body: data
        })
            .then(response => response.json())
            .then(callback)
            .catch(err => console.log(err));
    }

    render = (template, setWide = false) => {
        this.clear(setWide);
        this.popup.append(template);
    }

    clear = (setWide = false) => {

        if (setWide && !this.popup.classList.contains('custom-popup--wide')) {
            this.popup.classList.add('custom-popup--wide');
        } else {
            this.popup.classList.remove('custom-popup--wide');
        }

        const popupSection = this.popup.querySelector('.custom-popup-section');

        if (popupSection) popupSection.remove();
    }

    renderSmsCode = () => {
        const smsTemplate = document.querySelector('#login-sms').content.cloneNode(true);
        this.render(smsTemplate);

        const thisSection = this.popup.querySelector('.custom-popup-section');
        const passSubmit = thisSection.querySelector('.custom-popup-submit');
        const counterBlock = thisSection.querySelector('.counter-block');

        this.initCounter(counterBlock);

        this.addFormListeners(passSubmit, 'login_send_sms', ['sms'], this.renderProfile);
    }

    renderChangePhoneSmsCode = () => {
        const smsTemplate = document.querySelector('#change-phone-number-sms').content.cloneNode(true);
        this.render(smsTemplate);

        const thisSection = this.popup.querySelector('.custom-popup-section');
        const passSubmit = thisSection.querySelector('.custom-popup-submit');
        const counterBlock = thisSection.querySelector('.counter-block');

        this.initCounter(counterBlock);

        this.addFormListeners(passSubmit, 'change_phone_send_sms', ['sms']);
    }

    renderProfile = () => {
        const profileTemplate = document.querySelector('#login-profile').content.cloneNode(true);
        this.render(profileTemplate, true);

        const thisSection = this.popup.querySelector('.custom-popup-section');
        const profileSubmit = thisSection.querySelector('.custom-popup-submit');
        const phoneInp = thisSection.querySelector('input[type="tel"]');

        if (this.phoneNumber) phoneInp.value = this.phoneNumber;

        (0,_pass_view_switcher_js__WEBPACK_IMPORTED_MODULE_0__["default"])(thisSection);

        this.addFormListeners(profileSubmit, 'create_customer', [
            'login-phone',
            'login-email',
            'login-first-name',
            'login-second-name',
            'login-pass-first',
            'login-pass-second'
        ], this.renderProfile)
    }

    renderLoginPass = () => {
        const passTemplate = document.querySelector('#login-pass').content.cloneNode(true);
        this.render(passTemplate);

        const thisSection = this.popup.querySelector('.custom-popup-section');
        const passSubmit = thisSection.querySelector('.custom-popup-submit');
        const backlink = thisSection.querySelector('.login-popup-backlink');

        (0,_pass_view_switcher_js__WEBPACK_IMPORTED_MODULE_0__["default"])(thisSection);

        this.addBacklinkListener(backlink);
        this.addFormListeners(passSubmit, 'authorize', ['login', 'pass']);
    }

    addBacklinkListener = (link) => {
        link.onclick = (e) => {
            e.preventDefault();

            if (this.prevSection === 'send_code') {
                this.renderSendCode();
            }
            if (this.prevSection === 'send_sms') {
                this.renderSmsCode();
            }
        };
    }

    initCounter = (block) => {

        let count = 45;

        let timerId = setInterval(() => {

            block.innerHTML = '';

            if (count != 0) {
                block.innerHTML = `<p class="counter-text">Отправить код повторно можно через ${count}&nbsp;сек.</p>`;
                count--;
            } else {
                clearInterval(timerId);
                block.innerHTML = '<a class="counter-link" href="#">Отправить код повторно</a>';

                const counterLink = block.querySelector('.counter-link');

                counterLink.onclick = (e) => {
                    e.preventDefault();
                    counterLink.classList.add('loading');

                    this.ajaxSend('send_code_again', null, (res) => {
                        if (res['console_message']) {
                            console.log(res['console_message']);
                        }

                        if (res['success']) {
                            this.initCounter(block);
                        }
                    });
                }
            }
            
        }, 1000);
    }
}

function initPopups() {

    document.addEventListener("DOMContentLoaded", () => {

        const $accountLink = $('.account-link.logged-out, .login-link');

        const loginPopup = new Popup();

        const addClickListener = ($instance) => {
            let mouseDownEl;

            $instance.contentContainer.on('mousedown mouseup', function (e) {
                if ($instance.content) {
                    const content = $instance.content.get(0);

                    if (e.type == 'mousedown') mouseDownEl = e.target;

                    if (!(mouseDownEl == content || content.contains(mouseDownEl))) {
                        $instance.close();
                    }
                }
            })
        }

        $accountLink.magnificPopup({
            type: 'inline',
            midClick: true,
            closeOnBgClick: false,
            callbacks: {
                open: function () {
                    loginPopup.initLogin();
                    addClickListener(this);
                },
                close: function () {
                    loginPopup.clear();
                }
            }
        });

        const $changePhoneLink = $('.change-phone-number-link');

        if ($changePhoneLink) {
            const changePhonePopup = new Popup();

            $changePhoneLink.magnificPopup({
                type: 'inline',
                midClick: true,
                closeOnBgClick: false,
                callbacks: {
                    open: function () {
                        changePhonePopup.initChangePhone();
                        addClickListener(this);
                    },
                    close: function () {
                        changePhonePopup.clear();
                    }
                }
            });
        }

    });
}

/***/ }),

/***/ "./js/quantity-counter.js":
/*!********************************!*\
  !*** ./js/quantity-counter.js ***!
  \********************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ initQuantityCounter)
/* harmony export */ });
const quantityEl = document.querySelectorAll('div.product-quantity');

function initQuantityCounter() {

    if (quantityEl.length) {
        quantityEl.forEach((el) => {
            const input = el.querySelector('[type="number"]');
            const minus = el.querySelector('.product-quantity-minus');
            const plus = el.querySelector('.product-quantity-plus');
        
            minus.onclick = (e) => {
                e.preventDefault();

                if (input.value == 0) {
                    input.value = 0;
                } else {
                    input.value--;
                    $(input).trigger('input');
                }
            };
        
            plus.onclick = (e) => { 
                e.preventDefault();
                input.value++;
                $(input).trigger('input');
            };

        });
    }
}

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!********************!*\
  !*** ./js/main.js ***!
  \********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _ajax_wishlist_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ajax_wishlist.js */ "./js/ajax_wishlist.js");
/* harmony import */ var _catalog_mobile_slide_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./catalog_mobile_slide.js */ "./js/catalog_mobile_slide.js");
/* harmony import */ var _custom_select_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./custom_select.js */ "./js/custom_select.js");
/* harmony import */ var _filters_mobile_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./filters_mobile.js */ "./js/filters_mobile.js");
/* harmony import */ var _grid_switcher_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./grid-switcher.js */ "./js/grid-switcher.js");
/* harmony import */ var _attributes_list_mobile_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./attributes-list-mobile.js */ "./js/attributes-list-mobile.js");
/* harmony import */ var _quantity_counter_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./quantity-counter.js */ "./js/quantity-counter.js");
/* harmony import */ var _popup_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./popup.js */ "./js/popup.js");
/* harmony import */ var _phone_mask_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./phone-mask.js */ "./js/phone-mask.js");
/* harmony import */ var _pass_view_switcher_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./pass-view-switcher.js */ "./js/pass-view-switcher.js");
/* harmony import */ var _mobile_menu_js__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./mobile_menu.js */ "./js/mobile_menu.js");
/* harmony import */ var _mobile_search_js__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./mobile_search.js */ "./js/mobile_search.js");
/* harmony import */ var _modal_js__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./modal.js */ "./js/modal.js");
// import { Fancybox, Carousel, Panzoom } from "@fancyapps/ui";














(0,_ajax_wishlist_js__WEBPACK_IMPORTED_MODULE_0__["default"])();
new _catalog_mobile_slide_js__WEBPACK_IMPORTED_MODULE_1__["default"]();
(0,_custom_select_js__WEBPACK_IMPORTED_MODULE_2__["default"])();
(0,_filters_mobile_js__WEBPACK_IMPORTED_MODULE_3__["default"])();
(0,_grid_switcher_js__WEBPACK_IMPORTED_MODULE_4__["default"])();
(0,_attributes_list_mobile_js__WEBPACK_IMPORTED_MODULE_5__["default"])();
(0,_quantity_counter_js__WEBPACK_IMPORTED_MODULE_6__["default"])();
(0,_popup_js__WEBPACK_IMPORTED_MODULE_7__["default"])();
(0,_phone_mask_js__WEBPACK_IMPORTED_MODULE_8__["default"])();
(0,_pass_view_switcher_js__WEBPACK_IMPORTED_MODULE_9__["default"])(document);
(0,_mobile_menu_js__WEBPACK_IMPORTED_MODULE_10__["default"])();
(0,_mobile_search_js__WEBPACK_IMPORTED_MODULE_11__["default"])();
(0,_modal_js__WEBPACK_IMPORTED_MODULE_12__["default"])();
})();

/******/ })()
;
//# sourceMappingURL=script.js.map