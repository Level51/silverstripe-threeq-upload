import Vue from 'vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

import {
  faCheck, faCopy, faDownload, faFileVideo, faSpinner,
} from '@fortawesome/free-solid-svg-icons';

library.add(faCheck, faCopy, faDownload, faFileVideo, faSpinner);

Vue.component('FaIcon', FontAwesomeIcon);
