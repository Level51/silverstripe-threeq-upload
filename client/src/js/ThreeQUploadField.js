import Vue from 'vue';
import ThreeQUpload from 'src/App.vue';
import VueI18n from 'vue-i18n';
import watchElement from './util';
import { createStore } from './store';

Vue.use(VueI18n);

const i18n = new VueI18n({
  locale: 'en',
  fallbackLocale: 'en',
});

const render = (el) => {
  new Vue({
    store: createStore(),
    i18n,
    render(h) {
      return h(ThreeQUpload, {
        props: {
          serverPayload: JSON.parse(el.dataset.payload),
        },
      });
    },
  }).$mount(`#${el.id}`);
};

watchElement('.threeq-upload-field', (el) => {
  setTimeout(() => {
    render(el);
  }, 1);
});
