import * as types from './mutation-types';

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
  [types.SET_PREVIEW_STATE](state, previewState) {
    state.showPreview = previewState;
  },
};
