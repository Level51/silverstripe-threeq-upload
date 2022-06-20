export default {
  value(state) {
    return state.file?.id;
  },
  canEdit(state) {
    return !state.payload.isReadonly;
  }
};
