import * as types from './mutation-types';

export default {
  initStore({ commit, dispatch }, payload) {
    // Trigger a reset first to prevent keeping old data
    commit(types.RESET_STORE);

    commit(types.SET_PAYLOAD, payload);

    if (payload.file) {
      dispatch('setFile', payload.file);
      dispatch('showPreview');
    }
  },
  setFile({ commit }, file) {
    commit(types.SET_FILE, file);
  },
  setMessage({ commit }, message) {
    if (!message) {
      commit(types.SET_MESSAGE, null);
      return;
    }

    let sanitizedType = message.type;

    if (!sanitizedType) {
      sanitizedType = 'success';
    }

    if (sanitizedType === 'error') {
      sanitizedType = 'danger';
    }

    commit(types.SET_MESSAGE, {
      type: sanitizedType,
      content: message.content,
    });
  },
  showPreview({ commit }) {
    commit(types.SET_PREVIEW_VISBILITY, true);
  },
  hidePreview({ commit }) {
    commit(types.SET_PREVIEW_VISBILITY, false);
  },
  deleteFile({ dispatch }) {
    dispatch('setFile', null);
    dispatch('hidePreview');
  },
  setIsUploadRunningState({ commit }, isUploadRunning) {
    commit(types.SET_IS_UPLOAD_RUNNING_STATE, isUploadRunning);
  },
};
