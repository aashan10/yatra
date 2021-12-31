!function(){"use strict";var e={n:function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,{a:n}),n},d:function(t,n){for(var r in n)e.o(n,r)&&!e.o(t,r)&&Object.defineProperty(t,r,{enumerable:!0,get:n[r]})},o:function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}},t=window.wp.element,n=window.wp.blocks,r=window.wp.blockEditor,o=window.wp.components,a=window.wp.i18n,i=window.wp.serverSideRender,s=e.n(i);(0,n.registerBlockType)("yatra/destination",{apiVersion:2,title:(0,a.__)("Destination","yatra"),description:(0,a.__)("This block is used to show the destination list of Yatra WordPress plugin.","yatra"),icon:{foreground:"#1abc9c",src:"dashicons dashicons-location"},category:"yatra",edit:e=>{const{attributes:n,setAttributes:i}=e,l=(0,r.useBlockProps)();return(0,t.createElement)("div",l,(0,t.createElement)(s(),{block:"yatra/destination",attributes:n}),(0,t.createElement)(r.InspectorControls,{key:"setting"},(0,t.createElement)("div",{id:"yatra-destination-controls"},(0,t.createElement)(o.Panel,null,(0,t.createElement)(o.PanelBody,{title:(0,a.__)("Destination Settings","yatra"),initialOpen:!0},(0,t.createElement)(o.SelectControl,{label:(0,a.__)("Order","yatra"),value:n.order,options:[{label:(0,a.__)("Ascending","yatra"),value:"asc"},{label:(0,a.__)("Descending","yatra"),value:"desc"}],onChange:e=>i({order:e})}))))))}})}();