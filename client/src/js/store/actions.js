import * as types from './mutation-types';

export default {
  initStore({ commit, dispatch }, payload) {
    commit(types.SET_PAYLOAD, payload);

    if (payload.file) {
      dispatch('setFile', payload.file);
      dispatch('showPreview');
    }
  },
  setFile({ commit }, file) {
    commit(types.SET_FILE, file);
  },
  setMessage({ commit }, { type, content }) {
    let sanitizedType = type;

    if (!sanitizedType) {
      sanitizedType = 'success';
    }

    if (sanitizedType === 'error') {
      sanitizedType = 'danger';
    }

    commit(types.SET_MESSAGE, {
      type: sanitizedType,
      content,
    });
  },
  showPreview({ commit }) {
    commit(types.SET_PREVIEW_STATE, true);
  },
  hidePreview({ commit }) {
    commit(types.SET_PREVIEW_STATE, false);
  },
};
