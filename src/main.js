import './index.css'
import { mount } from 'svelte'
import App from './App.svelte'

// Ensure we only mount if the element exists (e.g., we might be on a page without the editor)
const target = document.getElementById('cms-editor')

if (target) {
  const app = mount(App, {
    target: target,
  })
}
