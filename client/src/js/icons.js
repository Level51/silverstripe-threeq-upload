import Vue from 'vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

import { faFileVideo } from '@fortawesome/free-solid-svg-icons';

library.add(faFileVideo);

Vue.component('FaIcon', FontAwesomeIcon);
