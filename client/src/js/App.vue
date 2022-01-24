<template>
  <div class="threeQUpload">
    <div
      v-if="message"
      class="alert"
      :class="[`alert-${message.type}`]">
      {{ message.content }}
    </div>

    <template v-if="showPreview">
      <FilePreview />
    </template>

    <template v-if="!showPreview">
      <ThreeQSelect />
    </template>

    <input
      type="hidden"
      :id="payload.id"
      :name="payload.name"
      :value="value">
  </div>
</template>

<script>
import axios from 'axios';
import { mapState, mapGetters, mapActions } from 'vuex';
import ThreeQSelect from './components/Select.vue';
import FilePreview from './components/FilePreview.vue';
import 'src/icons';
import * as locales from 'src/lang';

export default {
  props: {
    serverPayload: {
      type: Object,
      required: true,
    },
  },
  components: { ThreeQSelect, FilePreview },
  created() {
    this.init();
  },
  computed: {
    ...mapState(['payload', 'message', 'showPreview']),
    ...mapGetters(['value']),
  },
  methods: {
    ...mapActions(['initStore']),
    async init() {
      await this.initStore(this.serverPayload);
      this.setLocale();
    },
    setLocale() {
      const locale = this.payload.lang ?? 'en';

      if (this.$i18n) {
        this.$i18n.setLocaleMessage(locale, locales[locale]);
        this.$i18n.locale = locale;
      }
    },
  },
};
</script>
