import './bootstrap';
import './rotating-words.js';
import './google-map.js';
import './password-toggle.js';
import './firebase-auth.js';
import { homeEventCalendarData } from './home-event-calendar.js';
import { homeCoverflowGallery, homeHeroGallery } from './home-coverflow.js';

window.homeEventCalendarData = homeEventCalendarData;
window.homeCoverflowGallery = homeCoverflowGallery;
window.homeHeroGallery = homeHeroGallery;

// Alpine.js
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
