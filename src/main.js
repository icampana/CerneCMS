import './index.css'
import { mount } from 'svelte'
import App from './App.svelte'

import { initLightbox } from './lib/lightbox.js'

// Ensure we only mount if the element exists (e.g., we might be on a page without the editor)
const target = document.getElementById('cms-editor')

if (target) {
  const app = mount(App, {
    target: target,
  })
} else {
    // We are likely on the frontend, so verify if we need to init lightbox
    // We can just init it blindly as it checks for selectors
    initLightbox();
}
