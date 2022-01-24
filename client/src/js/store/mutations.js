import * as types from './mutation-types';
import {SET_PREVIEW_VISBILITY} from "./mutation-types";

export default {
  [types.SET_PAYLOAD](state, payload) {
    state.payload = payload;
  },
  [types.SET_FILE](state, file) {
    state.file = file;
  },
  [types.SET_MESSAGE](state, message) {
    state.message = message;
  },
  [types.SET_PREVIEW_VISBILITY](state, isVisible) {
    state.previewVisible = isVisible;
  },
};
