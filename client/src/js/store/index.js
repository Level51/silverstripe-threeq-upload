import Vue from 'vue';
import Vuex from 'vuex';
import { state } from './state';
import actions from './actions';
import mutations from './mutations';
import getters from './getters';

Vue.use(Vuex);

const createStore = () => new Vuex.Store({
  state: () => ({ ...state }),
  actions,
  mutations,
  getters,
});

export { createStore };
