import Vue from 'vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

import { faFileVideo, faSpinner } from '@fortawesome/free-solid-svg-icons';

library.add(faFileVideo, faSpinner);

Vue.component('FaIcon', FontAwesomeIcon);
