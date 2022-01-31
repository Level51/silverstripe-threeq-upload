import * as types from './mutation-types';
import { initialState } from './state';

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
  [types.SET_IS_UPLOAD_RUNNING_STATE](state, isUploadRunning) {
    state.isUploadRunning = isUploadRunning;
  },
  [types.RESET_STORE](state) {
    Object.keys(initialState).forEach((key) => {
      state[key] = initialState[key];
    });
  },
};
