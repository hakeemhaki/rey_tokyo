(window["webpackJsonp_rey"] = window["webpackJsonp_rey"] || []).push([["style-blocks"],{

/***/ "./inc/gutenberg/src/blocks/container-v1/styles/style.scss":
/*!*****************************************************************!*\
  !*** ./inc/gutenberg/src/blocks/container-v1/styles/style.scss ***!
  \*****************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./inc/gutenberg/src/blocks/spacer-v1/styles/style.scss":
/*!**************************************************************!*\
  !*** ./inc/gutenberg/src/blocks/spacer-v1/styles/style.scss ***!
  \**************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

}]);

/******/ (function(modules) { // webpackBootstrap
/******/ 	// install a JSONP callback for chunk loading
/******/ 	function webpackJsonpCallback(data) {
/******/ 		var chunkIds = data[0];
/******/ 		var moreModules = data[1];
/******/ 		var executeModules = data[2];
/******/
/******/ 		// add "moreModules" to the modules object,
/******/ 		// then flag all "chunkIds" as loaded and fire callback
/******/ 		var moduleId, chunkId, i = 0, resolves = [];
/******/ 		for(;i < chunkIds.length; i++) {
/******/ 			chunkId = chunkIds[i];
/******/ 			if(Object.prototype.hasOwnProperty.call(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 				resolves.push(installedChunks[chunkId][0]);
/******/ 			}
/******/ 			installedChunks[chunkId] = 0;
/******/ 		}
/******/ 		for(moduleId in moreModules) {
/******/ 			if(Object.prototype.hasOwnProperty.call(moreModules, moduleId)) {
/******/ 				modules[moduleId] = moreModules[moduleId];
/******/ 			}
/******/ 		}
/******/ 		if(parentJsonpFunction) parentJsonpFunction(data);
/******/
/******/ 		while(resolves.length) {
/******/ 			resolves.shift()();
/******/ 		}
/******/
/******/ 		// add entry modules from loaded chunk to deferred list
/******/ 		deferredModules.push.apply(deferredModules, executeModules || []);
/******/
/******/ 		// run deferred modules when all chunks ready
/******/ 		return checkDeferredModules();
/******/ 	};
/******/ 	function checkDeferredModules() {
/******/ 		var result;
/******/ 		for(var i = 0; i < deferredModules.length; i++) {
/******/ 			var deferredModule = deferredModules[i];
/******/ 			var fulfilled = true;
/******/ 			for(var j = 1; j < deferredModule.length; j++) {
/******/ 				var depId = deferredModule[j];
/******/ 				if(installedChunks[depId] !== 0) fulfilled = false;
/******/ 			}
/******/ 			if(fulfilled) {
/******/ 				deferredModules.splice(i--, 1);
/******/ 				result = __webpack_require__(__webpack_require__.s = deferredModule[0]);
/******/ 			}
/******/ 		}
/******/
/******/ 		return result;
/******/ 	}
/******/
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// object to store loaded and loading chunks
/******/ 	// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 	// Promise = chunk loading, 0 = chunk loaded
/******/ 	var installedChunks = {
/******/ 		"blocks": 0
/******/ 	};
/******/
/******/ 	var deferredModules = [];
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	var jsonpArray = window["webpackJsonp_rey"] = window["webpackJsonp_rey"] || [];
/******/ 	var oldJsonpFunction = jsonpArray.push.bind(jsonpArray);
/******/ 	jsonpArray.push = webpackJsonpCallback;
/******/ 	jsonpArray = jsonpArray.slice();
/******/ 	for(var i = 0; i < jsonpArray.length; i++) webpackJsonpCallback(jsonpArray[i]);
/******/ 	var parentJsonpFunction = oldJsonpFunction;
/******/
/******/
/******/ 	// add entry module to deferred list
/******/ 	deferredModules.push(["./inc/gutenberg/src/blocks.js","style-blocks"]);
/******/ 	// run deferred modules when ready
/******/ 	return checkDeferredModules();
/******/ })
/************************************************************************/
/******/ ({

/***/ "./inc/gutenberg/src/blocks.js":
/*!*************************************!*\
  !*** ./inc/gutenberg/src/blocks.js ***!
  \*************************************/
/*! exports provided: registerReyBlocks */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "registerReyBlocks", function() { return registerReyBlocks; });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _utils_block_helpers__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./utils/block-helpers */ "./inc/gutenberg/src/utils/block-helpers.js");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _blocks_container_v1__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./blocks/container-v1 */ "./inc/gutenberg/src/blocks/container-v1/index.js");
/* harmony import */ var _blocks_spacer_v1__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./blocks/spacer-v1 */ "./inc/gutenberg/src/blocks/spacer-v1/index.js");


/**
 * WordPress dependencies
 */
 // Categories Helper



 // Register Blocks



const blocksData = {
  slug: 'rey-blocks',
  title: 'REY BLOCKS',
  icon: Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__["SVG"], {
    height: "24",
    width: "24",
    viewBox: "0 0 78 40",
    version: "1.1",
    xmlns: "http://www.w3.org/2000/svg"
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__["Path"], {
    d: "M78,0.857908847 L68.673913,0.857908847 L63.5869565,15.1206434 L58.5,0.857908847 L49.173913,0.857908847 L59.4008152,24.9865952 L52.7086216,40 L62.0226252,40 L78,0.857908847 Z M8.47826087,5.63002681 L8.47826087,0.857908847 L0,0.857908847 L0,26.5951743 L8.47826087,26.5951743 L8.47826087,17.1045576 C8.47826087,12.922252 10.7038043,10.1340483 13.1413043,9.43699732 C14.6779891,9.0080429 16.2146739,8.95442359 17.8043478,9.43699732 L17.8043478,0 C13.0353261,0.321715818 10.2269022,1.93029491 8.47826087,5.63002681 Z M35.7146739,19.9463807 C34.7078804,19.9463807 33.701087,19.7855228 33.0652174,19.4101877 L48.1141304,10.2949062 C46.1535326,1.769437 39.6888587,0 36.0326087,0 C27.1834239,0 21.8315217,6.11260054 21.8315217,13.7265416 C21.8315217,21.3404826 27.1834239,27.4530831 36.0326087,27.4530831 C40.1127717,27.4530831 43.6100543,25.9517426 46.4184783,23.2171582 L42.0733696,17.4798928 C40.5366848,18.9276139 38.2581522,19.9463807 35.7146739,19.9463807 Z M36.0326087,7.50670241 C37.4103261,7.50670241 38.3641304,8.20375335 38.7880435,8.90080429 L29.9918478,14.2091153 C29.4619565,10.1876676 32.4293478,7.50670241 36.0326087,7.50670241 Z",
    fill: "#CD2323",
    fillRule: "nonzero"
  }))
};
/**
 * Function to register an individual block.
 *
 * @param {Object} block The block to be registered.
 *
 */

const registerBlock = block => {
  if (!block) {
    return;
  }

  let {
    category
  } = block;
  const {
    name,
    settings
  } = block;
  category = blocksData.slug;
  Object(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__["registerBlockType"])(name, {
    category,
    ...settings
  });
};
/**
 * Function to register blocks.
 */


const registerReyBlocks = () => {
  Object(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__["setCategories"])([...Object(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__["getCategories"])(), blocksData]);
  [_blocks_container_v1__WEBPACK_IMPORTED_MODULE_4__, _blocks_spacer_v1__WEBPACK_IMPORTED_MODULE_5__].forEach(registerBlock);
};
registerReyBlocks();

/***/ }),

/***/ "./inc/gutenberg/src/blocks/container-v1/block.json":
/*!**********************************************************!*\
  !*** ./inc/gutenberg/src/blocks/container-v1/block.json ***!
  \**********************************************************/
/*! exports provided: name, category, attributes, default */
/***/ (function(module) {

module.exports = JSON.parse("{\"name\":\"reycore/container-v1\",\"category\":\"rey-blocks\",\"attributes\":{\"align\":{\"type\":\"string\"},\"offsetAlign\":{\"type\":\"string\"},\"maxWidth\":{\"type\":\"number\"},\"backgroundColor\":{\"type\":\"string\"},\"textColor\":{\"type\":\"string\"},\"customBackgroundColor\":{\"type\":\"string\"},\"customTextColor\":{\"type\":\"string\"}}}");

/***/ }),

/***/ "./inc/gutenberg/src/blocks/container-v1/edit.js":
/*!*******************************************************!*\
  !*** ./inc/gutenberg/src/blocks/container-v1/edit.js ***!
  \*******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _inspector__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./inspector */ "./inc/gutenberg/src/blocks/container-v1/inspector.js");
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! classnames */ "./node_modules/classnames/index.js");
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(classnames__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/compose */ "@wordpress/compose");
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_compose__WEBPACK_IMPORTED_MODULE_5__);


/**
 * Internal dependencies
 */

/**
 * External dependencies
 */


/**
 * WordPress dependencies
 */



 // import { BlockAlignmentToolbar, BlockControls, InnerBlocks, InspectorControls } from '@wordpress/block-editor';


/**
 * Block edit function
 */

class Edit extends _wordpress_element__WEBPACK_IMPORTED_MODULE_0__["Component"] {
  render() {
    const {
      attributes,
      className,
      setAttributes,
      selected,
      isSelected,
      backgroundColor,
      textColor
    } = this.props;
    const {
      align,
      offsetAlign,
      maxWidth,
      preview
    } = attributes;
    let styles = {
      backgroundColor: backgroundColor.color,
      color: textColor.color
    };

    if (maxWidth) {
      styles['--max-width'] = `${maxWidth}px`;
      styles['width'] = '100%';
    }

    return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["Fragment"], null, isSelected && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_inspector__WEBPACK_IMPORTED_MODULE_1__["default"], this.props), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", {
      className: classnames__WEBPACK_IMPORTED_MODULE_2___default()(className, 'reyBlock-container-v1', {
        [`--offsetAlign-${offsetAlign}`]: offsetAlign !== '',
        'has-background': backgroundColor.color,
        'has-text-color': textColor.color,
        [backgroundColor.class]: backgroundColor.class,
        [textColor.class]: textColor.class,
        '--no-preview': preview
      }),
      "data-align": align,
      style: styles
    }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", {
      className: "reyBlock-containerInner"
    }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__["InnerBlocks"], {
      templateLock: false,
      renderAppender: () => !(selected && selected.innerBlocks.length > 0) ? Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__["InnerBlocks"].ButtonBlockAppender, null) : Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__["InnerBlocks"].DefaultBlockAppender, null)
    }))));
  }

}

/* harmony default export */ __webpack_exports__["default"] = (Object(_wordpress_compose__WEBPACK_IMPORTED_MODULE_5__["compose"])([Object(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__["withColors"])('backgroundColor', {
  textColor: 'color'
})])(Edit));

/***/ }),

/***/ "./inc/gutenberg/src/blocks/container-v1/index.js":
/*!********************************************************!*\
  !*** ./inc/gutenberg/src/blocks/container-v1/index.js ***!
  \********************************************************/
/*! exports provided: name, category, metadata, settings, attributes */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "name", function() { return name; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "category", function() { return category; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "settings", function() { return settings; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "attributes", function() { return attributes; });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _styles_editor_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./styles/editor.scss */ "./inc/gutenberg/src/blocks/container-v1/styles/editor.scss");
/* harmony import */ var _styles_style_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./styles/style.scss */ "./inc/gutenberg/src/blocks/container-v1/styles/style.scss");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./edit */ "./inc/gutenberg/src/blocks/container-v1/edit.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./block.json */ "./inc/gutenberg/src/blocks/container-v1/block.json");
var _block_json__WEBPACK_IMPORTED_MODULE_4___namespace = /*#__PURE__*/__webpack_require__.t(/*! ./block.json */ "./inc/gutenberg/src/blocks/container-v1/block.json", 1);
/* harmony reexport (default from named exports) */ __webpack_require__.d(__webpack_exports__, "metadata", function() { return _block_json__WEBPACK_IMPORTED_MODULE_4__; });
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./save */ "./inc/gutenberg/src/blocks/container-v1/save.js");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__);

//  Import CSS.


/**
 * Internal dependencies
 */




/**
 * WordPress dependencies
 */


/**
 * Block constants
 */

const {
  name,
  category,
  attributes
} = _block_json__WEBPACK_IMPORTED_MODULE_4__;
const settings = {
  /* translators: block name */
  title: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__["__"])('Container [rey]', 'reycore'),

  /* translators: block description */
  description: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__["__"])('Add other blocks inside and align them.', 'reycore'),
  icon: Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("svg", {
    width: "24px",
    height: "24px",
    viewBox: "0 0 24 24",
    version: "1.1",
    xmlns: "http://www.w3.org/2000/svg"
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("g", null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("polygon", {
    fill: "#000000",
    fillRule: "nonzero",
    points: "12.8 11.2 16 11.2 16 12.8 12.8 12.8 12.8 16 11.2 16 11.2 12.8 8 12.8 8 11.2 11.2 11.2 11.2 8 12.8 8"
  }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("rect", {
    stroke: "#CD2323",
    fill: "none",
    strokeWidth: "2",
    x: "1",
    y: "4",
    width: "22",
    height: "16",
    rx: "4"
  }))),
  keywords: ['reycore',
  /* translators: block keyword */
  Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__["__"])('layout', 'reycore'),
  /* translators: block keyword */
  Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__["__"])('row', 'reycore')],
  supports: {
    align: true,
    alignWide: true,
    alignFull: true
  },
  attributes,
  edit: _edit__WEBPACK_IMPORTED_MODULE_3__["default"],
  save: _save__WEBPACK_IMPORTED_MODULE_5__["default"]
};


/***/ }),

/***/ "./inc/gutenberg/src/blocks/container-v1/inspector.js":
/*!************************************************************!*\
  !*** ./inc/gutenberg/src/blocks/container-v1/inspector.js ***!
  \************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/compose */ "@wordpress/compose");
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_compose__WEBPACK_IMPORTED_MODULE_4__);


/**
 * WordPress dependencies
 */





const Inspector = props => {
  const {
    attributes,
    setAttributes,
    backgroundColor,
    setBackgroundColor,
    setTextColor,
    textColor
  } = props;
  const {
    offsetAlign,
    align,
    maxWidth,
    preview
  } = attributes;

  const setOffsetTo = value => {
    props.setAttributes({
      offsetAlign: value
    });
  };

  const offsetAlignOptions = [{
    value: '',
    label: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('None', 'rey-core')
  }, {
    value: 'semi',
    label: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('Semi Offset', 'rey-core')
  }, {
    value: 'full',
    label: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('Full Offset', 'rey-core')
  }];
  const canAlign = align === 'left' || align === 'right';
  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__["InspectorControls"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__["PanelBody"], {
    title: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('Container Settings', 'rey-core')
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__["ToggleControl"], {
    label: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('No Preview Outline', 'rey-core'),
    checked: !!preview,
    onChange: () => setAttributes({
      preview: !preview
    })
  }), canAlign && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__["SelectControl"], {
    label: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('Offset align', 'rey-core'),
    value: offsetAlign,
    onChange: setOffsetTo,
    options: offsetAlignOptions
  }), canAlign && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__["RangeControl"], {
    label: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('Block width', 'rey-core') + ' (px)',
    value: maxWidth ? parseInt(maxWidth) : '',
    onChange: nextMaxWidth => setAttributes({
      maxWidth: parseInt(nextMaxWidth)
    }),
    min: 100,
    max: 1000,
    step: 1,
    initialPosition: 220
  })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__["PanelColorSettings"], {
    title: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('Color settings', 'rey-core'),
    initialOpen: false,
    colorSettings: [{
      value: backgroundColor.color,
      onChange: setBackgroundColor,
      label: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('Background color', 'rey-core')
    }, {
      value: textColor.color,
      onChange: setTextColor,
      label: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('Text color', 'rey-core')
    }]
  }));
};

/* harmony default export */ __webpack_exports__["default"] = (Object(_wordpress_compose__WEBPACK_IMPORTED_MODULE_4__["compose"])([Object(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__["withColors"])('backgroundColor', {
  textColor: 'color'
})])(Inspector));

/***/ }),

/***/ "./inc/gutenberg/src/blocks/container-v1/save.js":
/*!*******************************************************!*\
  !*** ./inc/gutenberg/src/blocks/container-v1/save.js ***!
  \*******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! classnames */ "./node_modules/classnames/index.js");
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(classnames__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__);


/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */




const save = ({
  attributes
}) => {
  const {
    align,
    offsetAlign,
    maxWidth,
    backgroundColor,
    textColor,
    customBackgroundColor,
    customTextColor
  } = attributes;
  const backgroundClass = Object(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__["getColorClassName"])('background-color', backgroundColor);
  const textClass = Object(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__["getColorClassName"])('color', textColor);
  let styles = {
    backgroundColor: backgroundClass ? undefined : customBackgroundColor,
    color: textClass ? undefined : customTextColor
  };

  if (maxWidth) {
    styles['--max-width'] = `${maxWidth}px`;
    styles['width'] = '100%';
  }

  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", {
    className: classnames__WEBPACK_IMPORTED_MODULE_1___default()('reyBlock-container-v1', {
      [`--offsetAlign-${offsetAlign}`]: offsetAlign !== '',
      'has-background': backgroundColor || customBackgroundColor,
      'has-text-color': textColor || customTextColor,
      [textClass]: textClass,
      [backgroundClass]: backgroundClass
    }),
    "data-align": align,
    style: styles
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", {
    className: "reyBlock-containerInner"
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__["InnerBlocks"].Content, null)));
};

/* harmony default export */ __webpack_exports__["default"] = (save);

/***/ }),

/***/ "./inc/gutenberg/src/blocks/container-v1/styles/editor.scss":
/*!******************************************************************!*\
  !*** ./inc/gutenberg/src/blocks/container-v1/styles/editor.scss ***!
  \******************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./inc/gutenberg/src/blocks/spacer-v1/block.json":
/*!*******************************************************!*\
  !*** ./inc/gutenberg/src/blocks/spacer-v1/block.json ***!
  \*******************************************************/
/*! exports provided: name, category, attributes, default */
/***/ (function(module) {

module.exports = JSON.parse("{\"name\":\"reycore/spacer-v1\",\"category\":\"rey-blocks\",\"attributes\":{\"heightDesktop\":{\"type\":\"number\"},\"heightTablet\":{\"type\":\"number\"},\"heightMobile\":{\"type\":\"number\"}}}");

/***/ }),

/***/ "./inc/gutenberg/src/blocks/spacer-v1/edit.js":
/*!****************************************************!*\
  !*** ./inc/gutenberg/src/blocks/spacer-v1/edit.js ***!
  \****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _inspector__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./inspector */ "./inc/gutenberg/src/blocks/spacer-v1/inspector.js");
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! classnames */ "./node_modules/classnames/index.js");
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(classnames__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_3__);


/**
 * Internal dependencies
 */

/**
 * External dependencies
 */


/**
 * WordPress dependencies
 */



/**
 * Block edit function
 */

class Edit extends _wordpress_element__WEBPACK_IMPORTED_MODULE_0__["Component"] {
  render() {
    const {
      attributes,
      className,
      setAttributes,
      selected,
      isSelected
    } = this.props;
    const {
      heightDesktop,
      heightTablet,
      heightMobile
    } = attributes;
    let styles = {};

    if (heightDesktop) {
      styles['--height-lg'] = `${heightDesktop}px`;
    }

    if (heightTablet) {
      styles['--height-md'] = `${heightTablet}px`;
    }

    if (heightMobile) {
      styles['--height-sm'] = `${heightMobile}px`;
    }

    return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["Fragment"], null, isSelected && Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_inspector__WEBPACK_IMPORTED_MODULE_1__["default"], this.props), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", {
      className: classnames__WEBPACK_IMPORTED_MODULE_2___default()(className, 'reyBlock-spacer-v1', {}),
      style: styles
    }));
  }

}

/* harmony default export */ __webpack_exports__["default"] = (Edit);

/***/ }),

/***/ "./inc/gutenberg/src/blocks/spacer-v1/index.js":
/*!*****************************************************!*\
  !*** ./inc/gutenberg/src/blocks/spacer-v1/index.js ***!
  \*****************************************************/
/*! exports provided: name, category, metadata, settings, attributes */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "name", function() { return name; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "category", function() { return category; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "settings", function() { return settings; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "attributes", function() { return attributes; });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _styles_editor_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./styles/editor.scss */ "./inc/gutenberg/src/blocks/spacer-v1/styles/editor.scss");
/* harmony import */ var _styles_style_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./styles/style.scss */ "./inc/gutenberg/src/blocks/spacer-v1/styles/style.scss");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./edit */ "./inc/gutenberg/src/blocks/spacer-v1/edit.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./block.json */ "./inc/gutenberg/src/blocks/spacer-v1/block.json");
var _block_json__WEBPACK_IMPORTED_MODULE_4___namespace = /*#__PURE__*/__webpack_require__.t(/*! ./block.json */ "./inc/gutenberg/src/blocks/spacer-v1/block.json", 1);
/* harmony reexport (default from named exports) */ __webpack_require__.d(__webpack_exports__, "metadata", function() { return _block_json__WEBPACK_IMPORTED_MODULE_4__; });
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./save */ "./inc/gutenberg/src/blocks/spacer-v1/save.js");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__);

//  Import CSS.


/**
 * Internal dependencies
 */




/**
 * WordPress dependencies
 */


/**
 * Block constants
 */

const {
  name,
  category,
  attributes
} = _block_json__WEBPACK_IMPORTED_MODULE_4__;
const settings = {
  /* translators: block name */
  title: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__["__"])('Spacer [rey]', 'reycore'),

  /* translators: block description */
  description: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_6__["__"])('Add spaces between elements.', 'reycore'),
  icon: Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("svg", {
    width: "24px",
    height: "24px",
    viewBox: "0 0 24 24",
    version: "1.1",
    xmlns: "http://www.w3.org/2000/svg"
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("g", {
    id: "spacer",
    stroke: "none",
    strokeWidth: "1",
    fill: "none",
    fillRule: "evenodd"
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("path", {
    d: "M4,4 C2.34314575,4 1,5.34314575 1,7 L1,17 C1,18.6568542 2.34314575,20 4,20 L20,20 C21.6568542,20 23,18.6568542 23,17 L23,7 C23,5.34314575 21.6568542,4 20,4 L4,4 Z",
    stroke: "#CD2323",
    strokeWidth: "2"
  }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("rect", {
    fill: "#000000",
    x: "6",
    y: "10",
    width: "12",
    height: "1"
  }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("rect", {
    fill: "#000000",
    x: "6",
    y: "13",
    width: "12",
    height: "1"
  }))),
  keywords: ['reycore', 'space', 'distance'],
  supports: {},
  attributes,
  edit: _edit__WEBPACK_IMPORTED_MODULE_3__["default"],
  save: _save__WEBPACK_IMPORTED_MODULE_5__["default"]
};


/***/ }),

/***/ "./inc/gutenberg/src/blocks/spacer-v1/inspector.js":
/*!*********************************************************!*\
  !*** ./inc/gutenberg/src/blocks/spacer-v1/inspector.js ***!
  \*********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);


/**
 * WordPress dependencies
 */




const Inspector = props => {
  const {
    attributes,
    setAttributes
  } = props;
  const {
    heightDesktop,
    heightTablet,
    heightMobile
  } = attributes;
  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__["InspectorControls"], null, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__["PanelBody"], {
    title: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('Spacer Settings', 'rey-core')
  }, Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__["RangeControl"], {
    label: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('Desktop Height', 'rey-core') + ' (px)',
    value: heightDesktop ? parseInt(heightDesktop) : '',
    onChange: next => setAttributes({
      heightDesktop: parseInt(next)
    }),
    min: 0,
    max: 1000,
    step: 1,
    initialPosition: 20
  }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__["RangeControl"], {
    label: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('Tablet Height', 'rey-core') + ' (px)',
    value: heightTablet ? parseInt(heightTablet) : '',
    onChange: next => setAttributes({
      heightTablet: parseInt(next)
    }),
    min: 0,
    max: 1000,
    step: 1,
    initialPosition: 20
  }), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__["RangeControl"], {
    label: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__["__"])('Mobile Height', 'rey-core') + ' (px)',
    value: heightMobile ? parseInt(heightMobile) : '',
    onChange: next => setAttributes({
      heightMobile: parseInt(next)
    }),
    min: 0,
    max: 1000,
    step: 1,
    initialPosition: 20
  })));
};

/* harmony default export */ __webpack_exports__["default"] = (Inspector);

/***/ }),

/***/ "./inc/gutenberg/src/blocks/spacer-v1/save.js":
/*!****************************************************!*\
  !*** ./inc/gutenberg/src/blocks/spacer-v1/save.js ***!
  \****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! classnames */ "./node_modules/classnames/index.js");
/* harmony import */ var classnames__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(classnames__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__);


/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */



const save = props => {
  const {
    attributes,
    className
  } = props;
  const {
    heightDesktop,
    heightTablet,
    heightMobile
  } = attributes;
  let styles = {};

  if (heightDesktop) {
    styles['--height-lg'] = `${heightDesktop}px`;
  }

  if (heightTablet) {
    styles['--height-md'] = `${heightTablet}px`;
  }

  if (heightMobile) {
    styles['--height-sm'] = `${heightMobile}px`;
  }

  return Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])("div", {
    className: classnames__WEBPACK_IMPORTED_MODULE_1___default()(className, 'reyBlock-spacer-v1', {}),
    style: styles
  });
};

/* harmony default export */ __webpack_exports__["default"] = (save);

/***/ }),

/***/ "./inc/gutenberg/src/blocks/spacer-v1/styles/editor.scss":
/*!***************************************************************!*\
  !*** ./inc/gutenberg/src/blocks/spacer-v1/styles/editor.scss ***!
  \***************************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./inc/gutenberg/src/utils/block-helpers.js":
/*!**************************************************!*\
  !*** ./inc/gutenberg/src/utils/block-helpers.js ***!
  \**************************************************/
/*! exports provided: hasEmptyAttributes, supportsCollections, hasFormattingCategory */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "hasEmptyAttributes", function() { return hasEmptyAttributes; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "supportsCollections", function() { return supportsCollections; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "hasFormattingCategory", function() { return hasFormattingCategory; });
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/**
 * WordPress dependencies
 */

/**
 * Determine if the block attributes are empty.
 *
 * @param {Object} attributes The block attributes to check.
 * @return {boolean} The empty state of the attributes passed.
 */

const hasEmptyAttributes = attributes => {
  return !Object.entries(attributes).map(([, value]) => {
    if (typeof value === 'string') {
      value = value.trim();
    }

    if (value instanceof Array) {
      value = value.length;
    }

    if (value instanceof Object) {
      value = Object.entries(value).length;
    }

    return !!value;
  }).filter(value => value === true).length;
};
/**
 * Return bool depending on registerBlockCollection compatibility.
 *
 * @return {boolean} Value to indicate function support.
 */

const supportsCollections = () => {
  if (typeof _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__["registerBlockCollection"] === 'function') {
    return true;
  }

  return false;
};
/**
 * Check for which category to assign.
 *
 * @return {boolean} Value to indicate function support.
 */

const hasFormattingCategory = Object(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__["getCategories"])().some(function (category) {
  return category.slug === 'formatting';
});

/***/ }),

/***/ "./node_modules/classnames/index.js":
/*!******************************************!*\
  !*** ./node_modules/classnames/index.js ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/*!
  Copyright (c) 2018 Jed Watson.
  Licensed under the MIT License (MIT), see
  http://jedwatson.github.io/classnames
*/
/* global define */

(function () {
	'use strict';

	var hasOwn = {}.hasOwnProperty;

	function classNames() {
		var classes = [];

		for (var i = 0; i < arguments.length; i++) {
			var arg = arguments[i];
			if (!arg) continue;

			var argType = typeof arg;

			if (argType === 'string' || argType === 'number') {
				classes.push(arg);
			} else if (Array.isArray(arg)) {
				if (arg.length) {
					var inner = classNames.apply(null, arg);
					if (inner) {
						classes.push(inner);
					}
				}
			} else if (argType === 'object') {
				if (arg.toString === Object.prototype.toString) {
					for (var key in arg) {
						if (hasOwn.call(arg, key) && arg[key]) {
							classes.push(key);
						}
					}
				} else {
					classes.push(arg.toString());
				}
			}
		}

		return classes.join(' ');
	}

	if ( true && module.exports) {
		classNames.default = classNames;
		module.exports = classNames;
	} else if (true) {
		// register as 'classnames', consistent with npm package name
		!(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_RESULT__ = (function () {
			return classNames;
		}).apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
	} else {}
}());


/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = window["wp"]["blockEditor"]; }());

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = window["wp"]["blocks"]; }());

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = window["wp"]["components"]; }());

/***/ }),

/***/ "@wordpress/compose":
/*!*********************************!*\
  !*** external ["wp","compose"] ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = window["wp"]["compose"]; }());

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = window["wp"]["element"]; }());

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/*! no static exports found */
/***/ (function(module, exports) {

(function() { module.exports = window["wp"]["i18n"]; }());

/***/ })

/******/ });
//# sourceMappingURL=blocks.js.map