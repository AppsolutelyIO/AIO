/**
 * Tabler Theme Entry Point
 *
 * Loads Tabler core (includes Bootstrap 5), lazy loading, and theme components.
 * Uses vanilla JavaScript with Livewire for interactivity.
 * Component init is centralized in init.ts.
 */

import '@tabler/core';
import './components/lazy-loading';
import './init';

// Store locations exports openSmartMap (used by Blade)
import './components/store-locations';
