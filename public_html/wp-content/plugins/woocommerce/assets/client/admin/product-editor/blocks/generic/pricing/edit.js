"use strict";var __importDefault=this&&this.__importDefault||function(e){return e&&e.__esModule?e:{default:e}};Object.defineProperty(exports,"__esModule",{value:!0}),exports.Edit=void 0;const block_templates_1=require("@woocommerce/block-templates"),components_1=require("@woocommerce/components"),navigation_1=require("@woocommerce/navigation"),tracks_1=require("@woocommerce/tracks"),compose_1=require("@wordpress/compose"),element_1=require("@wordpress/element"),i18n_1=require("@wordpress/i18n"),components_2=require("@wordpress/components"),use_currency_input_props_1=require("../../../hooks/use-currency-input-props"),use_product_entity_prop_1=__importDefault(require("../../../hooks/use-product-entity-prop"));function Edit({attributes:e,context:{postType:o}}){const t=(0,block_templates_1.useWooBlockProps)(e),{property:r,label:n,help:c}=e,[p,s]=(0,use_product_entity_prop_1.default)(r,{postType:o,fallbackValue:""}),i=(0,use_currency_input_props_1.useCurrencyInputProps)({value:p||"",onChange:s}),_=c?(0,element_1.createInterpolateElement)(c,{PricingTab:(0,element_1.createElement)(components_1.Link,{href:(0,navigation_1.getNewPath)({tab:"pricing"}),onClick:()=>{(0,tracks_1.recordEvent)("product_pricing_help_click")}})}):null,u=(0,compose_1.useInstanceId)(components_2.BaseControl,"wp-block-woocommerce-product-pricing-field");return(0,element_1.createElement)("div",{...t},(0,element_1.createElement)(components_2.BaseControl,{id:u,help:_},(0,element_1.createElement)(components_2.__experimentalInputControl,{...i,id:u,name:r,label:n||(0,i18n_1.__)("Price","woocommerce")})))}exports.Edit=Edit;