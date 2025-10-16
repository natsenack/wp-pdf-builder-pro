// Expose React globally for WordPress environment
import React from '@wordpress/element';
import ReactDOM from 'react-dom';

window.React = React;
window.ReactDOM = ReactDOM;
