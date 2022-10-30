(()=>{"use strict";var e={n:t=>{var a=t&&t.__esModule?()=>t.default:()=>t;return e.d(a,{a}),a},d:(t,a)=>{for(var r in a)e.o(a,r)&&!e.o(t,r)&&Object.defineProperty(t,r,{enumerable:!0,get:a[r]})},o:(e,t)=>Object.prototype.hasOwnProperty.call(e,t)};const t=window.wp.element,a=window.wp.blocks,r=window.wp.blockEditor,n=window.wp.components,o=window.wp.i18n,l=window.wp.serverSideRender;var i=e.n(l);(0,a.registerBlockType)("yatra/activity",{apiVersion:2,title:(0,o.__)("Activity","yatra"),description:(0,o.__)("This block is used to show the activity list of Yatra WordPress plugin.","yatra"),icon:{foreground:"#1abc9c",src:"dashicons dashicons-universal-access"},category:"yatra",edit:e=>{const{attributes:a,setAttributes:l}=e,c=(0,r.useBlockProps)();return(0,t.createElement)("div",c,(0,t.createElement)(i(),{block:"yatra/activity",attributes:a}),(0,t.createElement)(r.InspectorControls,{key:"setting"},(0,t.createElement)("div",{id:"yatra-activity-controls"},(0,t.createElement)(n.Panel,null,(0,t.createElement)(n.PanelBody,{title:(0,o.__)("Activity Settings","yatra"),initialOpen:!0},(0,t.createElement)(n.SelectControl,{label:(0,o.__)("Order","yatra"),value:a.order,options:[{label:(0,o.__)("Ascending","yatra"),value:"asc"},{label:(0,o.__)("Descending","yatra"),value:"desc"}],onChange:e=>l({order:e})}),(0,t.createElement)(n.SelectControl,{label:(0,o.__)("Columns","yatra"),value:a.columns,options:[{label:(0,o.__)("Two (2)","yatra"),value:2},{label:(0,o.__)("Three (3)","yatra"),value:3},{label:(0,o.__)("Four (4)","yatra"),value:4}],onChange:e=>l({columns:e})}))))))}})})();